<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\RedirectResponse;

final class EnsureUserIsEnrolled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $courseRouteParameter The name of the route parameter that holds the course identifier (e.g., 'course' for {course:slug})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $courseRouteParameter = 'course'): Response
    {
        /** @var Course|null $course */
        $course = $request->route($courseRouteParameter);

        // If the route parameter does not resolve to a Course instance, or is not a Course model at all.
        if (!$course instanceof Course) {
            Log::error("EnsureUserIsEnrolled: Course model not found or invalid for route parameter '{$courseRouteParameter}'.", [
                'route' => $request->route() ? $request->route()->getName() : 'N/A',
                'parameters' => $request->route() ? $request->route()->parameters() : []
            ]);
            // Depending on policy, might abort or allow if course is not part of the context.
            // For strict enrollment check, a missing course implies an issue or misconfiguration.
            return $this->accessDeniedResponse($request, null, 'Course context not found.');
        }

        /** @var User|null $user */
        $user = Auth::user();

        // If user is not authenticated, Laravel's 'auth' middleware should handle redirection.
        // However, if this middleware is somehow reached without 'auth', deny access.
        if (!$user) {
            // This case should ideally be caught by 'auth' middleware applied before this one.
            return redirect()->guest(route('login')); 
        }

        // 1. Bypass for Privileged Roles (Admin)
        // Assuming User model has a hasRole() method.
        if ($user->hasRole('admin')) {
            Log::debug("EnsureUserIsEnrolled: Admin user {$user->id} granted access to course {$course->id}.");
            return $next($request);
        }

        // 2. Bypass for Course Teacher/Owner
        if ($course->user_id === $user->id) {
            Log::debug("EnsureUserIsEnrolled: Teacher user {$user->id} granted access to own course {$course->id}.");
            return $next($request);
        }

        // 3. Enrollment Check for regular users
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'active') // Crucial: only active enrollments grant access
            ->exists();

        if ($isEnrolled) {
            Log::debug("EnsureUserIsEnrolled: Enrolled user {$user->id} granted access to course {$course->id}.");
            return $next($request);
        }

        // 4. If not enrolled and not bypassed, deny access
        Log::warning("EnsureUserIsEnrolled: Access denied for user {$user->id} to course {$course->id}. Not enrolled or privileged.");
        return $this->accessDeniedResponse($request, $course, 'You do not have access to this course.');
    }

    /**
     * Helper to generate access denied response.
     * Can redirect back with error or return 403 for API requests.
     */
    private function accessDeniedResponse(Request $request, ?Course $course, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        $redirectRouteName = 'home'; // Default redirect route name
        $redirectParameters = [];

        if ($course) {
            $redirectRouteName = 'courses.show';
            $redirectParameters = [$course->slug];
        } elseif (!\Illuminate\Support\Facades\Route::has('home')) { // Check if 'home' route exists
            // If 'home' route doesn't exist and no course context, redirect to root.
            // This is a fallback, ideally 'home' or a similar named route should exist.
            return redirect('/')->with('error', $message);
        }
        
        return redirect()->route($redirectRouteName, $redirectParameters)->with('error', $message);
    }
} 