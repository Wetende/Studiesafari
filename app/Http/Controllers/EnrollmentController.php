<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User; // Assuming User model has activeSubscription method
// use App\Models\UserSubscription; // Not directly used if activeSubscription() on User returns it
// use App\Models\SubscriptionTier; // Not directly used if relations are set up correctly
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class EnrollmentController extends Controller
{
    /**
     * Enroll the authenticated user in a course via their active subscription.
     *
     * @param Request $request
     * @param Course $course
     * @return RedirectResponse
     */
    public function enrollViaSubscription(Request $request, Course $course): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Validation Checks
        if (!$course->is_published) {
            Log::warning("Attempt to enroll in unpublished course {$course->id} by user {$user->id}");
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'This course is not currently available for enrollment.');
        }

        if (is_null($course->required_subscription_tier_id)) {
            Log::warning("Attempt to enroll via subscription in course {$course->id} (no required tier) by user {$user->id}");
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'This course is not available for subscription-based enrollment.');
        }

        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($existingEnrollment) {
            return redirect()->route('courses.show', $course->slug)
                ->with('info', 'You are already enrolled in this course.');
        }

        // 2. Fetch user's active subscription and course's required tier
        // Assuming User model has an activeSubscription() method that returns the active UserSubscription model
        // And UserSubscription model has a `subscriptionTier` relationship to the SubscriptionTier model
        // And SubscriptionTier model has a `level` attribute (integer)
        // And Course model has a `requiredSubscriptionTier` relationship to the SubscriptionTier model
        $userActiveSubscription = $user->activeSubscription()->first(); // Example: $user->activeSubscription()->with('subscriptionTier')->first();

        if (!$userActiveSubscription || !$userActiveSubscription->subscriptionTier) {
            Log::info("User {$user->id} attempted enrollment in {$course->id} without an active/valid subscription tier.");
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'You do not have an active subscription or your subscription tier is invalid.');
        }

        $userTier = $userActiveSubscription->subscriptionTier;
        $courseRequiredTier = $course->requiredSubscriptionTier; // This relationship should exist on the Course model

        if (!$courseRequiredTier) {
            Log::error("Course {$course->id} has required_subscription_tier_id but failed to load requiredSubscriptionTier relationship.");
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'Could not verify course subscription requirements. Please try again later.');
        }

        // 3. Compare tier levels
        if ($userTier->level < $courseRequiredTier->level) {
            Log::info("User {$user->id} (tier: {$userTier->name}/{$userTier->level}) attempt to enroll in {$course->id} (requires tier: {$courseRequiredTier->name}/{$courseRequiredTier->level}) - insufficient tier.");
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'Your current subscription tier does not grant access to this course. Please upgrade your subscription.');
        }

        // 4. If eligible, create enrollment record
        try {
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrolled_at' => now(),
                'access_type' => 'subscription', // Enum value
                'status' => 'active',           // Enum value
                'course_purchase_id' => null,
            ]);

            Log::info("User {$user->id} successfully enrolled in course {$course->id} via subscription (tier: {$userTier->name}).");
            // TODO: Redirect to "Course Home" page (Phase 5) once its route is defined.
            // For now, redirecting back to course show page with success.
            return redirect()->route('courses.show', $course->slug)
                ->with('success', 'You have successfully enrolled in the course!');

        } catch (\Exception $e) {
            Log::error("Error creating enrollment for user {$user->id} in course {$course->id}: " . $e->getMessage());
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'An unexpected error occurred during enrollment. Please try again.');
        }
    }
} 