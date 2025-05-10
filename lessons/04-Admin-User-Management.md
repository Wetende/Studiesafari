# Subphase 4: Admin User Management

## Overview

A comprehensive Learning Management System requires robust administrative tools to manage users. This lesson explores the admin user management system we've built, which allows administrators to create, view, edit, and delete users with different roles.

## Admin Routes

The admin routes are protected by both authentication and role middleware to ensure only administrators can access them:

```php
// Admin routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Route for admin user management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
});
```

This creates a set of resourceful routes for user management with the following URLs:
- `GET /admin/users` - Index (list all users)
- `GET /admin/users/create` - Create form
- `POST /admin/users` - Store a new user
- `GET /admin/users/{user}` - Show a specific user
- `GET /admin/users/{user}/edit` - Edit form
- `PUT/PATCH /admin/users/{user}` - Update a user
- `DELETE /admin/users/{user}` - Delete a user

## User Controller for Admin

The `UserController` in the Admin namespace handles all user management operations:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final class UserController extends Controller
{
    // Controller methods here
}
```

Let's break down each of the controller methods:

## Listing Users (Index)

```php
/**
 * Display a listing of the resource.
 */
public function index(Request $request): View
{
    $query = User::query()
        ->with('roles')
        ->latest();

    // Filter by search term
    if ($request->has('search')) {
        $search = $request->get('search');
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Filter by role
    if ($request->has('role') && $request->get('role') !== 'all') {
        $role = $request->get('role');
        $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    $users = $query->paginate(15)
        ->withQueryString();

    return view('admin.users.index', [
        'users' => $users,
        'roles' => Role::all(),
    ]);
}
```

This method:

1. Starts a query builder for the User model, eager loading roles
2. Adds search filtering if a search term is provided
3. Adds role filtering if a role is selected
4. Paginates the results (15 per page)
5. Preserves the query string parameters for pagination links
6. Returns the view with users and roles data

## Creating a New User (Create and Store)

```php
/**
 * Show the form for creating a new resource.
 */
public function create(): View
{
    return view('admin.users.create', [
        'roles' => Role::all(),
    ]);
}

/**
 * Store a newly created resource in storage.
 */
public function store(Request $request): RedirectResponse
{
    $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'role' => ['required', 'exists:roles,name'],
    ]);

    $user = User::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // Assign role
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

    return redirect()->route('admin.users.index')
        ->with('success', 'User created successfully.');
}
```

The create method displays the form with available roles, while the store method:

1. Validates the form data
2. Creates a new user record
3. Assigns the selected role
4. Creates the appropriate profile based on the role
5. Redirects back to the user list with a success message

## Viewing a User (Show)

```php
/**
 * Display the specified resource.
 */
public function show(User $user): View
{
    $user->load('roles');
    
    return view('admin.users.show', [
        'user' => $user,
    ]);
}
```

This method loads the user with their roles and displays the user details view.

## Editing a User (Edit and Update)

```php
/**
 * Show the form for editing the specified resource.
 */
public function edit(User $user): View
{
    $user->load('roles');
    
    return view('admin.users.edit', [
        'user' => $user,
        'roles' => Role::all(),
        'userRoles' => $user->roles->pluck('name')->toArray(),
    ]);
}

/**
 * Update the specified resource in storage.
 */
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

    $user->update([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
    ]);

    // Update password if provided
    if ($request->filled('password')) {
        $user->update([
            'password' => Hash::make($request->password),
        ]);
    }

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

The edit method prepares data for the edit form, while the update method:

1. Validates the form data (note that password is now optional)
2. Updates the user's basic information
3. Updates the password only if a new one is provided
4. Syncs the user's roles (adding new ones and removing ones that were unchecked)
5. Ensures the user has the appropriate profiles for each role
6. Redirects back to the user list with a success message

## Deleting a User (Destroy)

```php
/**
 * Remove the specified resource from storage.
 */
public function destroy(User $user): RedirectResponse
{
    // Don't allow deleting yourself
    if ($user->id === Auth::id()) {
        return redirect()->route('admin.users.index')
            ->with('error', 'You cannot delete your own account.');
    }

    $user->delete();

    return redirect()->route('admin.users.index')
        ->with('success', 'User deleted successfully.');
}
```

This method:

1. Checks if the user is trying to delete their own account (prevented for safety)
2. Deletes the user record (related profiles are deleted via database cascading)
3. Redirects back to the user list with a success message

## Admin Views

The admin user management system includes several views:

### User List (Index)

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Management') }}
            </h2>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Add New User') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Search and Filter -->
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-grow">
                                <x-input-label for="search" :value="__('Search')" />
                                <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" :value="request('search')" placeholder="Search by name or email" />
                            </div>
                            <div class="w-full md:w-48">
                                <x-input-label for="role" :value="__('Filter by Role')" />
                                <select id="role" name="role" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <x-primary-button class="ml-3">
                                    {{ __('Filter') }}
                                </x-primary-button>
                                <a href="{{ route('admin.users.index') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                    {{ __('Reset') }}
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <!-- Users Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Name') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Email') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Roles') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr>
                                        <!-- User data cells -->
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No users found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

This view includes:
1. A header with a link to create a new user
2. A search and filter form
3. Success and error message displays
4. A table listing all users with columns for name, email, roles, status, and actions
5. Pagination controls

### Create User Form

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New User') }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                {{ __('Back to Users') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <!-- Form fields (first name, last name, email, role, password) -->
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Create User') }}
                            </x-primary-button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### Edit User Form

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit User') }}: {{ $user->name }}
            </h2>
            <div>
                <a href="{{ route('admin.users.show', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                    {{ __('View') }}
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Form fields (first name, last name, email, roles, password) -->
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

The key difference in the edit form is that it:
1. Uses the PUT method instead of POST
2. Pre-fills the form fields with the user's current data
3. Allows selecting multiple roles instead of just one
4. Makes the password field optional

### User Details View

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Details') }}: {{ $user->name }}
            </h2>
            <div>
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Basic Information') }}</h3>
                            
                            <div class="mt-4 border rounded-lg overflow-hidden">
                                <!-- User details (name, email, status, roles, etc.) -->
                            </div>
                        </div>

                        <!-- Role-Specific Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Role-Specific Information') }}</h3>
                            
                            <div class="mt-4 border rounded-lg overflow-hidden">
                                <!-- Student, teacher, or parent profile details -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delete button (not shown for self) -->
                    @if(Auth::id() !== $user->id)
                        <div class="mt-6 flex justify-end">
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    {{ __('Delete User') }}
                                </button>
                            </form>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

This view shows:
1. The user's basic information
2. Role-specific profile information, displayed conditionally based on the user's roles
3. A delete button (not shown for the user's own account)

## Key Features of the Admin User Management System

1. **Search and Filtering** - Administrators can search for users by name or email and filter by role
2. **Role Management** - Users can be assigned one or more roles, with corresponding profiles created automatically
3. **Password Management** - Administrators can set or reset user passwords
4. **Detailed View** - Administrators can see all user details, including role-specific profile information
5. **Account Protection** - Administrators can't delete their own accounts to prevent accidental lockout
6. **Success Feedback** - Success and error messages provide feedback on operations
7. **Pagination** - Results are paginated for better performance and usability

## Design Patterns Used

Several Laravel design patterns are used in the admin user management system:

1. **Resource Controller** - The UserController follows the resourceful controller pattern
2. **Route Model Binding** - Laravel automatically injects the User model based on the ID in the URL
3. **Query Builder Chaining** - The index method builds a complex query by chaining methods
4. **Eager Loading** - Relationships are eager loaded to prevent N+1 query problems
5. **Form Request Validation** - Validation rules are defined in one place
6. **Flash Messages** - Success and error messages are flashed to the session
7. **Model Events** - Deleting a user automatically deletes associated profiles (via database cascading)

## Conclusion

The admin user management system provides a robust interface for administrators to manage user accounts in the StudiesSafari LMS. By using Laravel's resource controller pattern and Blade templating system, we've created a clean, intuitive interface that allows administrators to perform all necessary user management tasks.

The system is designed to be both powerful and safe, with features like search, filtering, and pagination for usability, plus safeguards like preventing self-deletion to avoid administrative lockout. 