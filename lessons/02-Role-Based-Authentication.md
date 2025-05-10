# Subphase 2: Role-Based Authentication System

## Overview

In a Learning Management System like StudiesSafari, different user types (students, teachers, parents, administrators) need different permissions and access levels. This lesson explains how we've implemented a role-based authentication system using Laravel's middleware.

## Role Model and Database Structure

Our system uses a many-to-many relationship between users and roles. This allows a user to have multiple roles if needed (e.g., someone could be both a teacher and a parent).

The database structure includes:

1. `users` table - Stores user information
2. `roles` table - Stores available roles (student, teacher, parent, admin)
3. `role_user` pivot table - Stores which users have which roles

## Custom Role Middleware

The key to implementing role-based access is our custom middleware `EnsureUserHasRole`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if the user is authenticated
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // If no roles are specified, proceed
        if (empty($roles)) {
            return $next($request);
        }

        // Check if the user has any of the specified roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // User does not have any of the required roles
        abort(403, 'You do not have permission to access this resource.');
    }
}
```

Let's break down how this middleware works:

1. It first checks if the user is authenticated at all
2. If no roles are specified, it allows access (useful for routes that just need authentication)
3. It checks if the user has any of the specified roles
4. If the user has at least one of the required roles, they are allowed to proceed
5. If the user doesn't have any of the required roles, they are shown a 403 Forbidden error

## Registering the Middleware

We register this middleware in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    // Other middleware
    'role' => EnsureUserHasRole::class,
];
```

This allows us to use the shorthand `role:student,teacher` in our route definitions.

## User Model Role Methods

The User model needs methods to check for roles. A typical implementation would be:

```php
public function roles()
{
    return $this->belongsToMany(Role::class);
}

public function hasRole(string $role): bool
{
    return $this->roles->where('name', $role)->isNotEmpty();
}

public function isStudent(): bool
{
    return $this->hasRole('student');
}

public function isTeacher(): bool
{
    return $this->hasRole('teacher');
}

public function isParent(): bool
{
    return $this->hasRole('parent');
}

public function isAdmin(): bool
{
    return $this->hasRole('admin');
}
```

## Role-Based Registration

Our registration process includes role selection, as seen in the `RegisteredUserController`:

```php
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
```

## Role-Based Routes

The role middleware allows us to define routes that are only accessible to specific user roles:

```php
// Student routes
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Student-specific routes
});

// Teacher routes
Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // Teacher-specific routes
});

// Parent routes
Route::middleware(['auth', 'verified', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    // Parent-specific routes
});

// Admin routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin routes
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});
```

This route organization has several advantages:

1. **URL Structure** - Routes are prefixed with the role name (e.g., `/student/courses`, `/teacher/lessons`)
2. **Route Naming** - Routes are named with the role prefix (e.g., `student.courses.index`, `teacher.lessons.create`)
3. **Access Control** - Only users with the appropriate role can access these routes
4. **Code Organization** - Route definitions are logically grouped by user role

## Role-Specific Views

With this role-based system, we can conditionally display UI elements based on user roles:

```php
@if(Auth::user()->isAdmin())
    <a href="{{ route('admin.users.index') }}">User Management</a>
@endif

@if(Auth::user()->isTeacher())
    <a href="{{ route('teacher.courses.index') }}">My Courses</a>
@endif

@if(Auth::user()->isStudent())
    <a href="{{ route('student.enrollments.index') }}">My Enrollments</a>
@endif
```

## Role Management in Admin Panel

Our admin panel includes user management functionality with the ability to assign and edit user roles:

```php
public function update(Request $request, User $user): RedirectResponse
{
    $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
        'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        'roles' => ['required', 'array'],
        'roles.*' => ['exists:roles,name'],
    ]);

    // Update user data...

    // Sync user roles
    $roleIds = Role::whereIn('name', $request->roles)->pluck('id');
    $user->roles()->sync($roleIds);

    // Ensure user has appropriate profiles for their roles
    foreach ($request->roles as $roleName) {
        switch ($roleName) {
            case 'student':
                if (!$user->studentProfile) {
                    StudentProfile::create(['user_id' => $user->id]);
                }
                break;
            case 'teacher':
                if (!$user->teacherProfile) {
                    TeacherProfile::create(['user_id' => $user->id]);
                }
                break;
            case 'parent':
                if (!$user->parentProfile) {
                    ParentProfile::create(['user_id' => $user->id]);
                }
                break;
        }
    }

    return redirect()->route('admin.users.index')
        ->with('success', 'User updated successfully.');
}
```

## Conclusion

Our role-based authentication system provides a solid foundation for access control in the StudiesSafari LMS. By combining Laravel's authentication system with custom middleware and role-specific routes, we've created a flexible and secure system that:

1. Allows users to register with a specific role
2. Creates role-specific profiles for each user type
3. Restricts access to routes based on user roles
4. Provides a clean URL structure and route naming convention
5. Enables conditional UI rendering based on user roles
6. Supports multi-role users when necessary

In the next lesson, we'll explore the user profile management system, which leverages the role-based approach to provide customized profile forms for each user type. 