<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    /**
     * Display the role selection view.
     */
    public function showRoleSelection(): View
    {
        return view('auth.register-role-select');
    }

    /**
     * Handle role selection and redirect to the next step.
     */
    public function handleRoleSelection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['student', 'teacher', 'parent'])]
        ]);

        Session::put('registration_role', $validated['role']);

        switch ($validated['role']) {
            case 'student':
                return redirect()->route('register.student.code-check');
            case 'teacher':
                return redirect()->route('register.teacher');
            case 'parent':
                return redirect()->route('register.parent');
            default:
                return redirect()->route('register')->withErrors(['role' => 'Invalid role selected.']);
        }
    }

    // Student Registration Flow Methods - Phase 2

    public function showStudentCodeCheck(): View|RedirectResponse
    {
        if (Session::get('registration_role') !== 'student') {
            return redirect()->route('register');
        }
        return view('auth.register-student-code-check');
    }

    public function handleStudentCodeCheck(Request $request): RedirectResponse
    {
        if (Session::get('registration_role') !== 'student') {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'has_code' => ['required', 'string', Rule::in(['yes', 'no'])]
        ]);

        Session::put('student_has_code', $validated['has_code']);

        if ($validated['has_code'] === 'yes') {
            return redirect()->route('register.student.with-code');
        }
        return redirect()->route('register.student.no-code');
    }

    public function showStudentFormWithCode(): View|RedirectResponse
    {
        if (
            Session::get('registration_role') !== 'student' ||
            Session::get('student_has_code') !== 'yes'
        ) {
            if (Session::get('registration_role') !== 'student') {
                return redirect()->route('register');
            }
            return redirect()->route('register.student.code-check');
        }
        return view('auth.register-student-with-code');
    }

    public function showStudentFormWithoutCode(): View|RedirectResponse
    {
        if (
            Session::get('registration_role') !== 'student' ||
            Session::get('student_has_code') !== 'no'
        ) {
            if (Session::get('registration_role') !== 'student') {
                return redirect()->route('register');
            }
            return redirect()->route('register.student.code-check');
        }
        return view('auth.register-student-no-code');
    }

    public function registerStudent(Request $request): RedirectResponse
    {
        $registrationRole = Session::get('registration_role');
        $studentHasCode = Session::get('student_has_code');

        if ($registrationRole !== 'student' || !isset($studentHasCode)) {
            Session::flash('error', 'Invalid registration flow. Please start over.');
            return redirect()->route('register');
        }

        $validatedData = [];
        $user = null;

        DB::beginTransaction(); // Start transaction

        try {
            if ($studentHasCode === 'yes') {
                $validatedData = $request->validate([
                    'classroom_code' => ['required', 'string', 'max:255', 'exists:classrooms,join_code'], // Ensure classroom_code exists
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ]);

                $uniqueId = Str::random(8); // Generate a short unique ID
                $placeholderEmail = "student_code_{$uniqueId}@placeholder.lms.com";
                $placeholderName = "Student Temp {$uniqueId}";

                // Check if placeholder email already exists (highly unlikely but good practice)
                while (User::where('email', $placeholderEmail)->exists()) {
                    $uniqueId = Str::random(8);
                    $placeholderEmail = "student_code_{$uniqueId}@placeholder.lms.com";
                }
                 // Name also needs to be unique if there's a constraint, or just ensure it's filled
                $user = User::create([
                    'name' => $placeholderName, // `name` is not nullable
                    'email' => $placeholderEmail, // `email` is not nullable and unique
                    'password' => Hash::make($validatedData['password']),
                    'first_name' => null, // Nullable
                    'last_name' => null, // Nullable
                    'profile_incomplete' => true, // Flag for profile completion
                ]);

            } else { // No code
                $validatedData = $request->validate([
                    'first_name' => ['required', 'string', 'max:255'],
                    'last_name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ]);

                $user = User::create([
                    'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'], // `name` is not nullable
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'profile_incomplete' => false,
                ]);
            }

            // Assign 'student' role
            $studentRole = Role::where('name', 'student')->firstOrFail(); // Assuming 'student' role exists
            $user->roles()->attach($studentRole->id);

            // Create StudentProfile
            StudentProfile::create(['user_id' => $user->id]); // Add other fields if they are non-nullable and have defaults

            if ($studentHasCode === 'yes') {
                $classroom = Classroom::where('join_code', $validatedData['classroom_code'])->first();
                if ($classroom) { // Should always be true due to validation rule 'exists'
                    // Check if student is already in the classroom to prevent duplicates if necessary
                    if (!$classroom->students()->where('user_id', $user->id)->exists()) {
                         $classroom->students()->attach($user->id);
                    }
                } else {
                    // This case should ideally be prevented by 'exists' validation, but as a safeguard:
                    DB::rollBack();
                    return back()->withInput()->withErrors(['classroom_code' => 'Invalid classroom code provided.']);
                }
            }

            DB::commit(); // Commit transaction

            event(new Registered($user));
            Auth::login($user);

            // Clear registration session data
            Session::forget(['registration_role', 'student_has_code']);
            
            if ($studentHasCode === 'yes') {
                // Redirect to a profile completion page or dashboard with a prominent notice
                Session::flash('status', 'Registration successful! Please complete your profile.');
                // return redirect()->route('student.profile.edit'); // Example route for profile completion
            }

            return redirect()->route('dashboard');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception
            Log::error('Student Registration Error: ' . $e->getMessage());
            Session::flash('error', 'An unexpected error occurred during registration. Please try again.');
            return redirect()->route('register');
        }
    }

    // Teacher Registration Flow Methods - Phase 3
    public function showTeacherForm(): View|RedirectResponse
    {
        if (Session::get('registration_role') !== 'teacher') {
            return redirect()->route('register')->with('error', 'Please select your role first.');
        }
        return view('auth.register-teacher');
    }

    public function registerTeacher(Request $request): RedirectResponse
    {
        if (Session::get('registration_role') !== 'teacher') {
            Session::flash('error', 'Invalid registration flow. Please select your role again.');
            return redirect()->route('register');
        }

        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'profile_incomplete' => false, // Teachers complete profile at signup
            ]);

            $teacherRole = Role::where('name', 'teacher')->firstOrFail();
            $user->roles()->attach($teacherRole->id);

            TeacherProfile::create(['user_id' => $user->id]);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            Session::forget('registration_role');
            Session::flash('status', 'Teacher registration successful!');
            return redirect()->route('dashboard');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Teacher Registration Error: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            Session::flash('error', 'An unexpected error occurred during teacher registration. Please try again.');
            return redirect()->route('register');
        }
    }

    // Parent Registration Flow Methods - Phase 3
    public function showParentForm(): View|RedirectResponse
    {
        if (Session::get('registration_role') !== 'parent') {
            return redirect()->route('register')->with('error', 'Please select your role first.');
        }
        return view('auth.register-parent');
    }

    public function registerParent(Request $request): RedirectResponse
    {
        if (Session::get('registration_role') !== 'parent') {
            Session::flash('error', 'Invalid registration flow. Please select your role again.');
            return redirect()->route('register');
        }

        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'profile_incomplete' => false, // Parents complete profile at signup
            ]);

            $parentRole = Role::where('name', 'parent')->firstOrFail();
            $user->roles()->attach($parentRole->id);

            ParentProfile::create(['user_id' => $user->id]);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            Session::forget('registration_role');
            Session::flash('status', 'Parent registration successful!');
            return redirect()->route('dashboard');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Parent Registration Error: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            Session::flash('error', 'An unexpected error occurred during parent registration. Please try again.');
            return redirect()->route('register');
        }
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,teacher,parent'],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign the role to the user
        $role = Role::where('name', $request->role)->first();
        $user->roles()->attach($role);

        // Create the corresponding profile based on the role
        switch ($request->role) {
            case 'student':
                StudentProfile::create([
                    'user_id' => $user->id,
                ]);
                break;
            case 'teacher':
                TeacherProfile::create([
                    'user_id' => $user->id,
                ]);
                break;
            case 'parent':
                ParentProfile::create([
                    'user_id' => $user->id,
                ]);
                break;
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
