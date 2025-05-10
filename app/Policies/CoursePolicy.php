<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        // Admins can view any course
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $course->user_id;
        }

        // Students can view courses they're enrolled in or public courses
        if ($user->hasRole('student')) {
            // Check if course is published
            if (!$course->is_published) {
                return false;
            }

            // Check if student is enrolled
            return $user->enrollments()->where('course_id', $course->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        // Admins can update any course
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only update their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        // Admins can delete any course
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only delete their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the course.
     */
    public function restore(User $user, Course $course): bool
    {
        // Only admins can restore courses
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the course.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        // Only admins can permanently delete courses
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can publish/unpublish the course.
     */
    public function updateStatus(User $user, Course $course): bool
    {
        // Admins can update status of any course
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only update status of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can feature/unfeature the course.
     */
    public function toggleFeatured(User $user, Course $course): bool
    {
        // Only admins can feature/unfeature courses
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can recommend/unrecommend the course.
     */
    public function toggleRecommended(User $user, Course $course): bool
    {
        // Only admins can recommend/unrecommend courses
        return $user->hasRole('admin');
    }
} 