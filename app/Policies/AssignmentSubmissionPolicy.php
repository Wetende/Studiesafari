<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class AssignmentSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, AssignmentSubmission $submission): bool
    {
        // Admins can view any submission
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view submissions for assignments of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $submission->assignment->courseSection->course->user_id;
        }

        // Students can only view their own submissions
        if ($user->hasRole('student')) {
            return $user->id === $submission->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create a submission.
     */
    public function create(User $user, int $assignmentId): bool
    {
        // Only students can create submissions
        if (!$user->hasRole('student')) {
            return false;
        }

        // Get the assignment
        $assignment = \App\Models\Assignment::find($assignmentId);
        if (!$assignment) {
            return false;
        }

        $course = $assignment->courseSection->course;
        
        // Check if course and section are published
        if (!$course->is_published || !$assignment->courseSection->is_published) {
            return false;
        }

        // Check if student is enrolled
        return $user->enrollments()->where('course_id', $course->id)->exists();
    }

    /**
     * Determine whether the user can update the submission.
     */
    public function update(User $user, AssignmentSubmission $submission): bool
    {
        // Students can only update their own submissions if not graded yet
        if ($user->hasRole('student')) {
            if ($submission->grade !== null) {
                return false; // Can't update after grading
            }
            return $user->id === $submission->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the submission.
     */
    public function delete(User $user, AssignmentSubmission $submission): bool
    {
        // Admins can delete any submission
        if ($user->hasRole('admin')) {
            return true;
        }

        // Students can only delete their own submissions if not graded yet
        if ($user->hasRole('student')) {
            if ($submission->grade !== null) {
                return false; // Can't delete after grading
            }
            return $user->id === $submission->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, AssignmentSubmission $submission): bool
    {
        // Admins can grade any submission
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can grade submissions for assignments of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $submission->assignment->courseSection->course->user_id;
        }

        return false;
    }
} 