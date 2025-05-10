<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Policies\AssignmentPolicy;
use App\Policies\AssignmentSubmissionPolicy;
use App\Policies\CoursePolicy;
use App\Policies\CourseSectionPolicy;
use App\Policies\LessonPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\QuizPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        CourseSection::class => CourseSectionPolicy::class,
        Lesson::class => LessonPolicy::class,
        Quiz::class => QuizPolicy::class,
        Question::class => QuestionPolicy::class,
        Assignment::class => AssignmentPolicy::class,
        AssignmentSubmission::class => AssignmentSubmissionPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
