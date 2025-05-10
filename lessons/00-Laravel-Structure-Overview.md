# Laravel Structure Overview - StudiesSafari LMS

This document provides an overview of the Laravel framework structure as implemented in the StudiesSafari Learning Management System.

## Laravel Framework Structure

Laravel follows the Model-View-Controller (MVC) architectural pattern, which organizes code into three interconnected components. Our project adheres to Laravel 11 conventions and embraces modern PHP practices.

### Key Directories and Files

- **app/** - Contains the core code of the application
  - **Http/** - Houses controllers, middleware, and form requests
    - **Controllers/** - Contains controllers that handle HTTP requests
    - **Middleware/** - Contains classes that filter HTTP requests
    - **Requests/** - Contains form request validation classes
  - **Models/** - Contains Eloquent model classes that interact with database tables
  - **Providers/** - Contains service providers that bootstrap the application
  - **Services/** - Contains business logic services that controllers can use
  
- **bootstrap/** - Contains files that bootstrap the framework

- **config/** - Contains configuration files

- **database/** - Contains database migrations, seeders, and factories
  - **migrations/** - Contains database migration files that create/modify tables
  - **seeders/** - Contains database seeder classes that populate tables with data
  - **factories/** - Contains factory classes for generating test data

- **public/** - Web server entry point, contains front-end assets

- **resources/** - Contains views, front-end assets, and language files
  - **views/** - Contains Blade template files
  - **js/** - Contains JavaScript files
  - **css/** - Contains CSS files
  
- **routes/** - Contains route definition files
  - **web.php** - Contains web routes (for browser requests)
  - **api.php** - Contains API routes

- **storage/** - Contains compiled Blade templates, file uploads, and logs

- **tests/** - Contains automated tests

## Project-Specific Architecture

### Models

StudiesSafari LMS implements several key models representing core entities:

- **User** - Represents system users (students, teachers, parents, admins)
- **Role** - Represents user roles in the system
- **StudentProfile** - Contains student-specific data
- **TeacherProfile** - Contains teacher-specific data
- **ParentProfile** - Contains parent-specific data

### Controllers

Controllers are organized by functionality:

- **Auth/** - Authentication controllers
- **Admin/** - Administration controllers
- Each controller follows the resource controller pattern when appropriate (index, create, store, show, edit, update, destroy)

### Middleware

The system uses middleware for:

- Authentication control
- Role-based access control
- CSRF protection
- Other security features

### Routes

Routes are organized by user type and functionality:

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

## Naming Conventions

Following Laravel best practices and our development standards:

- **Classes**: PascalCase (e.g., `UserController`, `StudentProfile`)
- **Methods**: camelCase (e.g., `store()`, `updateProfile()`)
- **Variables**: camelCase (e.g., `$userRole`, `$studentData`)
- **Database tables**: snake_case, plural (e.g., `users`, `student_profiles`)
- **Routes**: kebab-case for URLs (e.g., `/user-management`)

## Code Organization Principles

1. **Single Responsibility Principle** - Each class has one responsibility
2. **Thin Controllers** - Business logic lives in services, not controllers
3. **Fat Models** - Models encapsulate data-related logic
4. **Form Requests** - Validation logic separated from controllers
5. **Middleware** - Request filtering occurs in middleware
6. **Policies** - Authorization logic defined in dedicated policy classes

## Summary

The StudiesSafari LMS follows Laravel's conventions while organizing code in a scalable and maintainable structure. By separating concerns and following consistent naming conventions, the codebase remains clean and extensible as new features are added. 