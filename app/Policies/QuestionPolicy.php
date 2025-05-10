<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the question.
     */
    public function view(User $user, Question $question): bool
    {
        // Admins can view any question
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view questions of their own quizzes
        if ($user->hasRole('teacher')) {
            return $user->id === $question->quiz->courseSection->course->user_id;
        }

        // Students can view questions in published quizzes of courses they're enrolled in
        if ($user->hasRole('student')) {
            $course = $question->quiz->courseSection->course;
            
            // Check if course and section are published
            if (!$course->is_published || !$question->quiz->courseSection->is_published) {
                return false;
            }

            // Check if student is enrolled and is attempting the quiz
            return $user->enrollments()->where('course_id', $course->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create questions.
     */
    public function create(User $user, int $quizId): bool
    {
        // Admins can create questions for any quiz
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can create questions for quizzes of their own courses
        if ($user->hasRole('teacher')) {
            return \App\Models\Quiz::where('id', $quizId)
                ->whereHas('courseSection.course', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        // Admins can update any question
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only update questions of their own quizzes
        if ($user->hasRole('teacher')) {
            return $user->id === $question->quiz->courseSection->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the question.
     */
    public function delete(User $user, Question $question): bool
    {
        // Admins can delete any question
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only delete questions of their own quizzes
        if ($user->hasRole('teacher')) {
            return $user->id === $question->quiz->courseSection->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can reorder question options.
     */
    public function reorderOptions(User $user, Question $question): bool
    {
        // Admins can reorder options for any question
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can reorder options for questions of their own quizzes
        if ($user->hasRole('teacher')) {
            return $user->id === $question->quiz->courseSection->course->user_id;
        }

        return false;
    }
    
    /**
     * Determine whether the user can view their own questions library.
     */
    public function viewLibrary(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('teacher');
    }
} 