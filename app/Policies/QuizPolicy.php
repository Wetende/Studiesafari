<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class QuizPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the quiz.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        // Admins can view any quiz
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view quizzes of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $quiz->courseSection->course->user_id;
        }

        // Students can view published quizzes of courses they're enrolled in
        if ($user->hasRole('student')) {
            $course = $quiz->courseSection->course;
            
            // Check if course and section are published
            if (!$course->is_published || !$quiz->courseSection->is_published) {
                return false;
            }

            // Check if student is enrolled
            return $user->enrollments()->where('course_id', $course->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create quizzes.
     */
    public function create(User $user, int $courseSectionId): bool
    {
        // Admins can create quizzes for any section
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can create quizzes for sections of their own courses
        if ($user->hasRole('teacher')) {
            return \App\Models\CourseSection::where('id', $courseSectionId)
                ->whereHas('course', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the quiz.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        // Admins can update any quiz
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only update quizzes of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $quiz->courseSection->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the quiz.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        // Admins can delete any quiz
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only delete quizzes of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $quiz->courseSection->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can reorder quiz questions.
     */
    public function reorderQuestions(User $user, Quiz $quiz): bool
    {
        // Admins can reorder questions for any quiz
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can reorder questions for quizzes of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $quiz->courseSection->course->user_id;
        }

        return false;
    }
    
    /**
     * Determine whether the user can manage quiz questions.
     */
    public function manageQuestions(User $user, Quiz $quiz): bool
    {
        // Admins can manage questions for any quiz
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can manage questions for quizzes of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $quiz->courseSection->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can attempt the quiz.
     */
    public function attempt(User $user, Quiz $quiz): bool
    {
        // Only students can attempt quizzes
        if (!$user->hasRole('student')) {
            return false;
        }

        $course = $quiz->courseSection->course;
        
        // Check if course and section are published
        if (!$course->is_published || !$quiz->courseSection->is_published) {
            return false;
        }

        // Check if student is enrolled
        return $user->enrollments()->where('course_id', $course->id)->exists();
    }
} 