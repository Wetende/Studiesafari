<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class LessonPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the lesson.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        // Admins can view any lesson
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view lessons of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $lesson->courseSection->course->user_id;
        }

        // Students can view published lessons of courses they're enrolled in
        if ($user->hasRole('student')) {
            $course = $lesson->courseSection->course;
            
            // If allowed to preview, anyone can view
            if ($lesson->is_preview_allowed) {
                return true;
            }
            
            // Check if course and section are published
            if (!$course->is_published || !$lesson->courseSection->is_published) {
                return false;
            }

            // Check if student is enrolled
            if (!$user->enrollments()->where('course_id', $course->id)->exists()) {
                return false;
            }
            
            // Check drip content rules if applicable
            // This is a simplified check - real implementation would be more complex
            if ($lesson->unlock_date && now() < $lesson->unlock_date) {
                return false;
            }
            
            // If we passed all checks, student can view the lesson
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create lessons.
     */
    public function create(User $user, int $courseSectionId): bool
    {
        // Admins can create lessons for any section
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can create lessons for sections of their own courses
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
     * Determine whether the user can update the lesson.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        // Admins can update any lesson
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only update lessons of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $lesson->courseSection->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        // Admins can delete any lesson
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only delete lessons of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $lesson->courseSection->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can reorder lessons.
     */
    public function reorder(User $user, int $courseSectionId): bool
    {
        // Admins can reorder lessons for any section
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can reorder lessons for sections of their own courses
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
     * Determine if the user can manage lesson attachments
     */
    public function manageAttachments(User $user, Lesson $lesson): bool
    {
        // Admins can manage attachments for any lesson
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only manage attachments for lessons of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $lesson->courseSection->course->user_id;
        }

        return false;
    }
} 