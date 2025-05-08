# StudiesSafari - Learning Management System

StudiesSafari is an accessible, role-based Learning Management System (LMS) designed for high school students, teachers, and parents in Uganda. It aims to deliver educational content, assessments, and engagement tools, inspired by platforms like CodeMonkey, with a focus on usability, scalability, and local relevance.

## Key Features

-   **Role-Based Access:**
    -   **Students:** Access courses based on subscription tier or direct purchase, take quizzes, view personalized recommendations, track progress, enroll in courses.
    -   **Teachers:** Create and manage courses (setting subscription requirements and/or direct purchase price), monitor student progress, earn based on defined compensation models.
    -   **Parents:** Monitor student performance, view progress reports, receive notifications.
-   **Content Delivery:**
    -   Structured **Courses** containing lessons in various formats (text, images, PDFs, videos).
    -   Support for uploaded images as lesson content or test questions.
    -   Embedded videos and live class integration (potentially as part of courses).
-   **Assessments and Quizzes:**
    -   Interactive quizzes within courses.
    -   Automated grading and feedback (potentially gated by subscription).
    -   Progress tracking within enrolled courses.
-   **Course Enrollment & Access:**
    -   Logged-in users can browse courses.
    -   Courses can be accessed via:
        -   **Subscription:** If the user's subscription tier meets or exceeds the course's requirement.
        -   **Direct Purchase:** If the course has a purchase price set (mandatory for 'purchase-only' courses, optional for subscription-based courses).
    -   Teachers determine if a course is part of the subscription system (by setting a required tier) or purchase-only (by not setting a required tier).
-   **Personalized Recommendations:**
    -   Suggest courses or lessons based on student performance, interests, and enrolled courses.
-   **Rankings and Gamification:**
    -   Leaderboards (potentially course-specific or platform-wide).
    -   Badges or rewards for completing courses or achieving high scores.
-   **Parental Monitoring:**
    -   Dashboards for parents to track student activity, enrolled courses, grades, and attendance.
-   **Content Management:**
    -   Teachers can upload, edit, and organize course content (lessons, quizzes, etc.).
    -   Admin panel for managing users, roles, subscriptions, courses, and platform settings.

## Business Model

StudiesSafari utilizes a hybrid monetization strategy combining tiered subscriptions with optional direct course purchases:

-   **Tiered Subscriptions:** The core revenue stream. Users subscribe to different tiers (e.g., Free, Bronze, Gold, Platinum) granting access to enroll in courses designated for their tier or lower.
    -   **Free Tier:** Limited access to introductory content/courses.
    -   **Paid Tiers:** Unlock access to enroll in a wider range of courses and potentially other platform features.
-   **Course Access Models:**
    1.  **Subscription-Based:** Teacher sets a `required_subscription_tier`. Users at or above this tier access via subscription. Teacher *can optionally* set a `price` allowing direct purchase by non-subscribers or those below the required tier.
    2.  **Purchase-Only:** Teacher does *not* set a `required_subscription_tier` but *must* set a `price`. The course is only available via direct purchase, regardless of user subscription status.
-   **Teacher Compensation:** The method depends on how a student gained access to a course:
    -   **Accessed via Subscription:** Teacher compensation is based on metrics derived from active, subscribed student enrollments in their course (e.g., a payout per enrolled subscriber over a period).
    -   **Accessed via Direct Purchase:** Teacher receives a pre-defined percentage share of the `price` paid by the user for that specific course purchase.

## Tech Stack

-   Laravel (PHP Framework)
-   Educrat HTML/CSS/JS Template (Frontend)
-   MySQL (Database)
-   Apache (Web Server)
-   Git (Version Control)

## Getting Started

Follow these steps to set up the project locally:

1.  **Prerequisites:**
    *   PHP (>= appropriate version for chosen Laravel, e.g., 8.1)
    *   Composer ([https://getcomposer.org/](https://getcomposer.org/))
    *   Node.js & npm ([https://nodejs.org/](https://nodejs.org/))
    *   MySQL
    *   Apache (or another web server compatible with PHP/Laravel)
    *   Git

2.  **Clone the Repository:**
    ```bash
    git clone https://github.com/Wetende/Learning-Management-System.git
    cd Learning-Management-System
    ```

3.  **Install Dependencies:**
    *   Install PHP dependencies:
        ```bash
        composer install
        ```
    *   Install frontend dependencies (if using npm for the template):
        ```bash
        # Check if package.json exists and run if needed
        # npm install
        # npm run build # Or npm run dev
        ```

4.  **Environment Configuration:**
    *   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    *   Generate the application key:
        ```bash
        php artisan key:generate
        ```
    *   Configure your `.env` file with your database credentials (DB\_DATABASE, DB\_USERNAME, DB\_PASSWORD) and any other necessary settings (e.g., APP\_URL).

5.  **Database Setup:**
    *   Ensure your MySQL server is running.
    *   Create a database for the application (e.g., `studysafari_lms`).
    *   Run the database migrations:
        ```bash
        php artisan migrate
        ```
    *   (Optional) Run database seeders if available:
        ```bash
        # php artisan db:seed
        ```

6.  **Web Server Configuration:**
    *   **Apache:** Configure a virtual host to point to the `public` directory of the project. Ensure `mod_rewrite` is enabled.
    *   **Laravel Development Server:** For quick testing, you can use:
        ```bash
        php artisan serve
        ```
        Then access the application at `http://127.0.0.1:8000` or the specified address.

7.  **Access the Application:**
    Open your web browser and navigate to the URL configured in your web server or the development server URL.

