# Subphase 1: Laravel Authentication Setup

## Overview

Authentication is the cornerstone of any web application, especially a Learning Management System where different users need different levels of access. Laravel provides robust authentication scaffolding out of the box, which we've customized for StudiesSafari LMS.

## Laravel Authentication Components

Laravel's authentication system consists of several key components:

1. **Authentication Guards** - Define how users are authenticated (session, token, etc.)
2. **User Providers** - Define how user information is retrieved (database, API, etc.)
3. **Middleware** - Protect routes that require authentication
4. **Password Reset** - Allow users to reset forgotten passwords

## Authentication Controllers

Laravel provides several controllers to handle authentication:

- **RegisteredUserController** - Handles user registration
- **AuthenticatedSessionController** - Handles login and logout
- **PasswordResetLinkController** - Handles sending password reset links
- **NewPasswordController** - Handles setting new passwords
- **EmailVerificationController** - Handles email verification

## Implementation in StudiesSafari LMS

Let's look at the `RegisteredUserController` as an example:

```php
class RegisteredUserController extends Controller
{
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
```

Let's break down what this controller does:

1. **Registration Form Display** - The `create()` method displays the registration form
2. **Form Validation** - The `store()` method validates the submitted form data
3. **User Creation** - Creates a new user record in the database
4. **Role Assignment** - Assigns the selected role to the user
5. **Profile Creation** - Creates a role-specific profile for the user
6. **Event Dispatch** - Triggers the `Registered` event (which can trigger email verification)
7. **Automatic Login** - Logs the newly registered user in
8. **Redirection** - Redirects to the dashboard

## Authentication Middleware

Laravel uses middleware to protect routes that require authentication. The key middleware for authentication is:

```php
'auth' => \App\Http\Middleware\Authenticate::class,
```

This middleware is applied to routes that should only be accessible to authenticated users. For example:

```php
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Other protected routes
});
```

## Authentication Configuration

Authentication configuration is stored in `config/auth.php`, which defines:

- Guards (session, api, etc.)
- User providers
- Password broker configuration

## Helpers and Facades

Laravel provides several helpers and facades for authentication:

- `Auth::user()` - Gets the currently authenticated user
- `Auth::check()` - Checks if a user is authenticated
- `Auth::attempt()` - Attempts to authenticate a user
- `Auth::login()` - Manually logs in a user
- `Auth::logout()` - Logs out the current user

## Authentication Flow

1. **Registration**:
   - User submits registration form
   - Data is validated
   - User record is created
   - Role is assigned
   - User is automatically logged in

2. **Login**:
   - User submits login form
   - Credentials are validated
   - User session is created
   - User is redirected to intended page

3. **Email Verification** (optional):
   - Verification email is sent to user
   - User clicks verification link
   - Email is marked as verified

4. **Password Reset**:
   - User requests password reset
   - Reset email is sent
   - User sets new password
   - User is redirected to login

## Conclusion

Laravel's authentication system provides a robust foundation for implementing user authentication in the StudiesSafari LMS. By extending the base system, we've added role-based registration and custom profile creation to tailor the authentication process to our specific needs.

In the next lesson, we'll dive deeper into how we've implemented role-based access control using Laravel's middleware system. 