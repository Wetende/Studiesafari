<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CourseSectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view all sections for a course.
     */
    public function viewAny(User $user, Course $course): bool
    {
        // Admins can view sections for any course
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view sections of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $course->user_id;
        }

        // Students can view published sections of courses they're enrolled in
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
     * Determine whether the user can view the section.
     */
    public function view(User $user, CourseSection $section): bool
    {
        // Admins can view any section
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can view sections of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $section->course->user_id;
        }

        // Students can view published sections of courses they're enrolled in
        if ($user->hasRole('student')) {
            // Check if course and section are published
            if (!$section->course->is_published || !$section->is_published) {
                return false;
            }

            // Check if student is enrolled
            return $user->enrollments()->where('course_id', $section->course_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create sections.
     */
    public function create(User $user, Course $course): bool
    {
        // Admins can create sections for any course
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can create sections for their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the section.
     */
    public function update(User $user, CourseSection $section): bool
    {
        // Admins can update any section
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only update sections of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $section->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the section.
     */
    public function delete(User $user, CourseSection $section): bool
    {
        // Admins can delete any section
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can only delete sections of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $section->course->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can reorder sections.
     */
    public function reorder(User $user, Course $course): bool
    {
        // Admins can reorder sections for any course
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can reorder sections of their own courses
        if ($user->hasRole('teacher')) {
            return $user->id === $course->user_id;
        }

        return false;
    }
} 