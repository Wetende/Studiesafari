# User Authentication System Documentation

## 1. Overview

The User Authentication system for StudiesSafari LMS is built upon Laravel's robust authentication services, customized to support distinct user roles: Student, Teacher, and Parent. It provides secure user registration, login, and password management functionalities. A key feature is the multi-step registration process tailored to gather role-specific information and guide users through a clear onboarding experience.



## 2. Core Functionalities

-   **Role-Based Multi-Step Registration:** Separate registration paths for Students, Teachers, and Parents.
-   **Secure Login:** Standard email and password authentication.
-   **Password Hashing:** Passwords are securely hashed using bcrypt.
-   **CSRF Protection:** All authentication forms are protected against Cross-Site Request Forgery.
-   **Session Management:** Laravel's session handling is used to maintain user login state.
-   **Password Recovery:** Standard Laravel password reset functionality (forgot password, reset link via email).

## 3. Registration Flow

The registration process begins with role selection and then branches into specific steps for each role.

### 3.1. Step 1: Role Selection
-   **Route:** `GET /register`
-   **Controller Method:** `RegisteredUserController@showRoleSelection`
-   **View:** `resources/views/auth/register-role-select.blade.php`
-   **Description:** Users are presented with three options (Student, Teacher, Parent) in a card-based UI. Clicking a role submits the choice.
-   **Submission:** `POST /register/select-role` handled by `RegisteredUserController@handleRoleSelection`. The selected role is stored in the session, and the user is redirected to the next appropriate step.

### 3.2. Step 2: Student Registration

#### 3.2.1. Student - Code Check
-   **Condition:** If 'student' role was selected.
-   **Route:** `GET /register/student/code-check`
-   **Controller Method:** `RegisteredUserController@showStudentCodeCheck`
-   **View:** `resources/views/auth/register-student-code-check.blade.php`
-   **Description:** Students are asked if they have a classroom code (card-based UI for "Yes" or "No").
-   **Submission:** `POST /register/student/handle-code-check` handled by `RegisteredUserController@handleStudentCodeCheck`. The choice is stored in the session.

#### 3.2.2. Student - With Classroom Code
-   **Condition:** If student selected 'Yes' to having a code.
-   **Route:** `GET /register/student/with-code`
-   **Controller Method:** `RegisteredUserController@showStudentFormWithCode`
-   **View:** `resources/views/auth/register-student-with-code.blade.php`
-   **Description:** Form for Classroom Code and Password.
-   **Submission:** `POST /register/student` handled by `RegisteredUserController@registerStudent`.
    -   User is created with placeholder `email` and `name` (derived from a unique identifier).
    -   `first_name` and `last_name` are initially null.
    -   User is flagged for profile completion (`profile_incomplete = true`).
    -   Student role is assigned, `StudentProfile` is created.
    -   Student is linked to the classroom via the `join_code`.

#### 3.2.3. Student - Without Classroom Code
-   **Condition:** If student selected 'No' to having a code.
-   **Route:** `GET /register/student/no-code`
-   **Controller Method:** `RegisteredUserController@showStudentFormWithoutCode`
-   **View:** `resources/views/auth/register-student-no-code.blade.php`
-   **Description:** Form for First Name, Last Name, Email, and Password.
-   **Submission:** `POST /register/student` handled by `RegisteredUserController@registerStudent`.
    -   User is created with provided details.
    -   `name` is concatenated from first and last names.
    -   Student role is assigned, `StudentProfile` is created.

### 3.3. Step 2: Teacher Registration
-   **Condition:** If 'teacher' role was selected in Step 1.
-   **Route:** `GET /register/teacher`
-   **Controller Method:** `RegisteredUserController@showTeacherForm`
-   **View:** `resources/views/auth/register-teacher.blade.php`
-   **Description:** Form for First Name, Last Name, Email, and Password.
-   **Submission:** `POST /register/teacher` handled by `RegisteredUserController@registerTeacher`.
    -   User is created with provided details.
    -   Teacher role is assigned, `TeacherProfile` is created.

### 3.4. Step 2: Parent Registration
-   **Condition:** If 'parent' role was selected in Step 1.
-   **Route:** `GET /register/parent`
-   **Controller Method:** `RegisteredUserController@showParentForm`
-   **View:** `resources/views/auth/register-parent.blade.php`
-   **Description:** Form for First Name, Last Name, Email, and Password.
-   **Submission:** `POST /register/parent` handled by `RegisteredUserController@registerParent`.
    -   User is created with provided details.
    -   Parent role is assigned, `ParentProfile` is created.

### 3.5. Post-Registration
-   Upon successful registration, a `Registered` event is dispatched.
-   The user is automatically logged in.
-   Relevant session data used during registration is cleared.
-   Users are redirected to their dashboard (e.g., `route('dashboard')`).
-   Students registering "with code" are shown a status message prompting them to complete their profile.

## 4. Login Flow

-   **Route:** `GET /login`
-   **Controller:** `App\Http\Controllers\Auth\AuthenticatedSessionController` (Standard Laravel Breeze/Jetstream controller)
-   **View:** `resources/views/auth/login.blade.php` (Standard Laravel Breeze/Jetstream view)
-   **Description:** Users enter their email and password.
-   **Submission:** `POST /login` handled by `AuthenticatedSessionController@store`.
-   **Post-Login:** Users are redirected to their intended page or the dashboard.

## 5. Logout Flow

-   **Route:** `POST /logout`
-   **Controller:** `App\Http\Controllers\Auth\AuthenticatedSessionController`
-   **Description:** Invalidates the user's session and logs them out.
-   **Post-Logout:** Users are redirected to the home page or login page.

## 6. Password Recovery

Standard Laravel password recovery is utilized:
-   Request Password Reset Link:
    -   `GET /forgot-password` (view)
    -   `POST /forgot-password` (sends reset link)
-   Reset Password:
    -   `GET /reset-password/{token}` (view with token)
    -   `POST /reset-password` (updates password)

## 7. Key Files & Directories

-   **Routes:** `routes/auth.php`
-   **Controller:** `app/Http/Controllers/Auth/RegisteredUserController.php` (for custom registration logic)
-   **Controllers (Standard):** `app/Http/Controllers/Auth/` (for login, password reset, etc., typically provided by Laravel Breeze/Jetstream)
-   **Models:**
    -   `app/Models/User.php`
    -   `app/Models/Role.php` (Assumed for role management)
    -   `app/Models/StudentProfile.php`
    -   `app/Models/TeacherProfile.php`
    -   `app/Models/ParentProfile.php`
    -   `app/Models/Classroom.php` (Used in student "with code" registration)
-   **Views:**
    -   `resources/views/auth/register-role-select.blade.php`
    -   `resources/views/auth/register-student-code-check.blade.php`
    -   `resources/views/auth/register-student-with-code.blade.php`
    -   `resources/views/auth/register-student-no-code.blade.php`
    -   `resources/views/auth/register-teacher.blade.php`
    -   `resources/views/auth/register-parent.blade.php`
    -   `resources/views/auth/login.blade.php`
    -   `resources/views/auth/forgot-password.blade.php`
    -   `resources/views/auth/reset-password.blade.php`
-   **Layouts/Components:** `resources/views/layouts/guest.blade.php` and various Blade components (e.g., for input fields, buttons) are used.

## 8. Security Considerations

-   **Password Hashing:** Laravel's `Hash` facade (bcrypt) is used for all user passwords.
-   **CSRF Protection:** Laravel's built-in CSRF protection is active on all POST forms.
-   **Input Validation:** All incoming request data is validated using Laravel's validation services.
-   **SQL Injection Prevention:** Eloquent ORM is used, which provides protection against SQL injection.
-   **Session Security:** Laravel's session management includes security features like session regeneration.
-   **Rate Limiting:** Consider adding rate limiting to login and password reset attempts to prevent brute-force attacks (Laravel provides tools for this).

## 9. Future Enhancements (Related to Phase 1)

-   **Profile Completion Flow:** Implement a dedicated interface for students who registered "with code" to complete their profile details (first name, last name, actual email if desired).
-   **Role & Permission Middleware:** Implement robust middleware for checking user roles and permissions to protect routes and actions.
-   **Admin User Management:** Develop interfaces for administrators to manage users, assign roles, and oversee accounts.
-   **Dedicated Form Requests:** Refactor validation logic into dedicated Form Request classes for each registration step/form.
-   **Socialite Integration:** (As noted in previous plans, this is a separate consideration for future integration if desired).
-   **Two-Factor Authentication (2FA):** For enhanced security, 2FA could be considered.

This document will be updated as the authentication system evolves. 