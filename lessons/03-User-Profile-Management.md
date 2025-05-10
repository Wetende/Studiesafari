# Subphase 3: User Profile Management

## Overview

A Learning Management System requires different profile information for different user types. For example, teachers need to store their qualifications, while students need to record their grade level. This lesson explores how we've implemented a flexible profile management system that adapts to different user roles.

## Profile Models

Our system uses separate profile models for each user type:

1. **StudentProfile** - Stores student-specific information
2. **TeacherProfile** - Stores teacher-specific information
3. **ParentProfile** - Stores parent-specific information

Each profile model has a one-to-one relationship with the User model:

```php
// In User model
public function studentProfile()
{
    return $this->hasOne(StudentProfile::class);
}

public function teacherProfile()
{
    return $this->hasOne(TeacherProfile::class);
}

public function parentProfile()
{
    return $this->hasOne(ParentProfile::class);
}
```

## Profile Controller

The `ProfileController` handles profile management across all user types. Let's look at how it loads the appropriate profile data:

```php
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
```

This method:

1. Loads the authenticated user with their roles
2. Initializes profile variables to null
3. Checks each role and loads the appropriate profile if it exists
4. Creates a new profile instance if the user has a role but no profile yet
5. Passes all profiles to the view, which will only show the relevant ones

## Form Request Validation

We use Laravel's Form Request Validation to handle profile updates. This keeps our controller clean and centralizes our validation rules:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            
            // Student profile fields
            'student_profile.date_of_birth' => ['nullable', 'date'],
            'student_profile.grade_level_id' => ['nullable', 'exists:grade_levels,id'],
            'student_profile.school_name' => ['nullable', 'string', 'max:255'],
            'student_profile.learning_interests' => ['nullable', 'array'],
            
            // Teacher profile fields
            'teacher_profile.bio' => ['nullable', 'string', 'max:1000'],
            'teacher_profile.qualifications' => ['nullable', 'string', 'max:1000'],
            'teacher_profile.school_affiliation' => ['nullable', 'string', 'max:255'],
            'teacher_profile.position' => ['nullable', 'string', 'max:255'],
            'teacher_profile.hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'teacher_profile.available_for_tutoring' => ['nullable', 'boolean'],
            
            // Parent profile fields
            'parent_profile.occupation' => ['nullable', 'string', 'max:255'],
            'parent_profile.relationship_to_student' => ['nullable', 'string', 'max:255'],
            'parent_profile.notification_preferences' => ['nullable', 'array'],
        ];
    }
}
```

This form request:

1. Defines validation rules for basic user information
2. Includes rules for the profile picture upload
3. Defines nested validation rules for each profile type
4. Makes all profile fields nullable, since a user may not have all profile types

## Profile Update Method

The `update` method in the `ProfileController` handles profile updates for all user types:

```php
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
```

This method:

1. Updates the basic user information
2. Resets the email verification timestamp if the email has changed
3. Handles profile picture uploads, including deleting the old picture
4. For each role the user has, updates or creates the corresponding profile

## Profile View

The profile edit view adapts to show only the relevant sections for each user's roles:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Common user information section -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Role-specific profile sections -->
            @if($studentProfile)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.student-profile-form')
                    </div>
                </div>
            @endif

            @if($teacherProfile)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.teacher-profile-form')
                    </div>
                </div>
            @endif

            @if($parentProfile)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.parent-profile-form')
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

This uses Blade conditionals to include only the relevant profile forms based on the user's roles.

## Account Deletion

The `ProfileController` also handles account deletion:

```php
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
```

This method:

1. Validates the current password to confirm deletion
2. Cleans up profile pictures from storage
3. Logs the user out
4. Deletes the user record (and any related profiles via database cascading)
5. Invalidates the session and regenerates the CSRF token

## Benefits of This Approach

This approach to profile management has several advantages:

1. **Separation of Concerns** - Each profile type is a separate model
2. **Flexibility** - Users can have multiple roles and corresponding profiles
3. **Clean UI** - Users only see profile sections relevant to their roles
4. **Validation** - Each profile type has its own validation rules
5. **Maintainability** - New profile types can be added without modifying existing code

## Conclusion

Our profile management system builds on the role-based authentication to provide a flexible, role-specific user experience. By using separate profile models for each user type and conditionally displaying profile sections, we ensure that users only see and update information relevant to their roles.

In the next lesson, we'll explore the admin user management system, which provides administrative control over user accounts, roles, and profiles. 