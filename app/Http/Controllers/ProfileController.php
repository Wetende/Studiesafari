<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('roles');

        $studentProfile = null;
        $teacherProfile = null;
        $parentProfile = null;

        if ($user->isStudent()) {
            $studentProfile = $user->studentProfile ?? new StudentProfile();
        }

        if ($user->isTeacher()) {
            $teacherProfile = $user->teacherProfile ?? new TeacherProfile();
        }

        if ($user->isParent()) {
            $parentProfile = $user->parentProfile ?? new ParentProfile();
        }

        return view('profile.edit', [
            'user' => $user,
            'studentProfile' => $studentProfile,
            'teacherProfile' => $teacherProfile,
            'parentProfile' => $parentProfile,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Update basic user information
        $user->fill($request->safe()->only(['first_name', 'last_name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($user->profile_picture_path) {
                Storage::disk('public')->delete($user->profile_picture_path);
            }
            
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture_path = $path;
        }

        $user->save();

        // Update role-specific profiles
        if ($user->isStudent() && $request->has('student_profile')) {
            $studentProfile = $user->studentProfile ?? new StudentProfile(['user_id' => $user->id]);
            $studentProfile->fill($request->get('student_profile'));
            $studentProfile->save();
        }

        if ($user->isTeacher() && $request->has('teacher_profile')) {
            $teacherProfile = $user->teacherProfile ?? new TeacherProfile(['user_id' => $user->id]);
            $teacherProfile->fill($request->get('teacher_profile'));
            $teacherProfile->save();
        }

        if ($user->isParent() && $request->has('parent_profile')) {
            $parentProfile = $user->parentProfile ?? new ParentProfile(['user_id' => $user->id]);
            $parentProfile->fill($request->get('parent_profile'));
            $parentProfile->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete profile picture if exists
        if ($user->profile_picture_path) {
            Storage::disk('public')->delete($user->profile_picture_path);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
