<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use App\Models\CoursePurchase;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // For potential JSON responses
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // For database transactions if needed
use Illuminate\Support\Str; // For generating a unique reference if needed

final class CoursePurchaseController extends Controller
{
    /**
     * Initiate the purchase process for a course.
     *
     * @param Request $request
     * @param Course $course
     * @return RedirectResponse|JsonResponse
     */
    public function initiatePurchase(Request $request, Course $course): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Validation Checks
        if (!$course->is_published) {
            Log::warning("Attempt to purchase unpublished course {$course->id} by user {$user->id}");
            return $this->purchaseErrorResponse($course, 'This course is not currently available for purchase.');
        }

        if ($course->price <= 0) {
            Log::warning("Attempt to purchase non-priced course {$course->id} (price: {$course->price}) by user {$user->id}");
            return $this->purchaseErrorResponse($course, 'This course cannot be purchased directly. It might be free or subscription-only.');
        }

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($isEnrolled) {
            return $this->purchaseInfoResponse($course, 'You are already enrolled in this course.');
        }

        // Check for existing pending or completed payment for this specific course by this user
        $existingPayment = Payment::where('user_id', $user->id)
            ->where('payable_id', $course->id)
            ->where('payable_type', Course::class)
            ->whereIn('status', ['pending', 'completed'])
            ->exists();

        if ($existingPayment) {
            // If completed, they should be enrolled (covered by above check). If pending, inform them.
            $pendingPayment = Payment::where('user_id', $user->id)
                ->where('payable_id', $course->id)
                ->where('payable_type', Course::class)
                ->where('status', 'pending')->first();
            if ($pendingPayment) {
                 return $this->purchaseInfoResponse($course, 'You already have a pending payment for this course. Please complete or cancel it.');
            }
            // If it reached here and existingPayment was true, it means it was a completed payment but they are not enrolled, which is an inconsistency.
            // However, the active enrollment check should catch successfully completed purchases.
            // This primarily guards against re-initiating a PENDING payment.
        }

        // 2. Record Creation (within a transaction)
        try {
            return DB::transaction(function () use ($user, $course) {
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'amount' => $course->price,
                    'currency' => config('app.currency', 'USD'), // Get currency from config
                    'status' => 'pending',
                    'payment_gateway' => 'simulated', // Placeholder for actual gateway name
                    'gateway_reference_id' => 'SIM-' . Str::uuid()->toString(), // Simulated unique ref
                    'payable_type' => Course::class,
                    'payable_id' => $course->id,
                ]);

                CoursePurchase::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'payment_id' => $payment->id,
                    // platform_fee and teacher_payout can be calculated later or upon payment completion
                ]);

                Log::info("Payment {$payment->id} initiated for course {$course->id} by user {$user->id}. Amount: {$payment->amount} {$payment->currency}.");

                // Simulation: Redirect to a page confirming initiation or return JSON
                // In a real scenario, this would be a redirect to the payment gateway.
                // TODO: Create a dedicated route/view for this simulated confirmation if redirecting.
                return response()->json([
                    'message' => 'Purchase initiated successfully. Please complete the payment.',
                    'payment_id' => $payment->id,
                    'course_id' => $course->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    // 'redirect_url' => route('payment.simulate.show', $payment->id) // Example if we had a simulation page
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error("Error initiating purchase for course {$course->id} by user {$user->id}: " . $e->getMessage());
            // In case of transaction failure or other exception
            return $this->purchaseErrorResponse($course, 'Could not initiate purchase due to an unexpected error. Please try again.', 500);
        }
    }

    private function purchaseErrorResponse(Course $course, string $message, int $statusCode = 422): RedirectResponse|JsonResponse
    {
        // Depending on API vs Web context, choose response type
        if (request()->expectsJson()) {
            return response()->json(['message' => $message, 'course_id' => $course->id], $statusCode);
        }
        return redirect()->route('courses.show', $course->slug)->with('error', $message);
    }

    private function purchaseInfoResponse(Course $course, string $message): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => $message, 'course_id' => $course->id], 200);
        }
        return redirect()->route('courses.show', $course->slug)->with('info', $message);
    }

    /**
     * Handle incoming webhook notifications from the payment gateway.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleGatewayWebhook(Request $request): JsonResponse
    {
        Log::info('Received payment gateway webhook.', $request->all());

        // IMPORTANT: In a real application, VALIDATE THE WEBHOOK SIGNATURE HERE!
        // This is crucial for security to ensure the webhook is genuinely from your payment gateway.
        // $isValid = $this->validateWebhook($request);
        // if (!$isValid) {
        //     Log::warning('Invalid webhook signature.');
        //     return response()->json(['error' => 'Invalid signature'], 403);
        // }

        // Simulate extracting necessary info from webhook payload
        // For this simulation, we expect 'payment_id' and 'event_type' (e.g., 'payment.succeeded', 'payment.failed')
        $payload = $request->all();
        $paymentId = $payload['payment_id'] ?? null;
        $eventType = $payload['event_type'] ?? null;
        $simulatedGatewayReference = $payload['gateway_reference_id'] ?? null; // For simulation

        if (!$paymentId || !$eventType) {
            Log::info("Webhook received with missing payment_id or event_type.", $payload);
            return response()->json(['error' => 'Missing payment_id or event_type'], 400);
        }

        $payment = Payment::find($paymentId);

        if (!$payment) {
            Log::warning("Webhook received for non-existent payment_id: {$paymentId}", $payload);
            return response()->json(['error' => 'Payment not found'], 404); // Or 200 OK to prevent retries from gateway if preferred
        }

        // Prevent processing already completed/failed payments multiple times from webhook
        if (in_array($payment->status, ['completed', 'failed'])) {
            Log::info("Webhook for already processed payment_id: {$paymentId}, status: {$payment->status}");
            return response()->json(['message' => 'Payment already processed']);
        }

        DB::beginTransaction();
        try {
            switch ($eventType) {
                case 'payment.succeeded': // Simulated success event
                    $payment->status = 'completed';
                    $payment->paid_at = now();
                    if ($simulatedGatewayReference) { // Store actual gateway ref if provided
                        $payment->gateway_reference_id = $simulatedGatewayReference;
                    }
                    $payment->save();

                    $coursePurchase = CoursePurchase::where('payment_id', $payment->id)->first();

                    if (!$coursePurchase) {
                        Log::error("Critical: Payment {$payment->id} completed but no associated CoursePurchase found.");
                        DB::rollBack();
                        // This is a critical issue, might need manual intervention or specific alerting
                        return response()->json(['error' => 'Internal server error: Course purchase record not found.'], 500);
                    }

                    // Create enrollment record
                    Enrollment::create([
                        'user_id' => $payment->user_id,
                        'course_id' => $coursePurchase->course_id,
                        'enrolled_at' => now(),
                        'access_type' => 'purchase', // Enum value
                        'status' => 'active',       // Enum value
                        'course_purchase_id' => $coursePurchase->id,
                    ]);

                    Log::info("Payment {$payment->id} successfully processed (completed). User {$payment->user_id} enrolled in course {$coursePurchase->course_id}.");
                    // Optional: Dispatch an event (e.g., PaymentCompleted, UserEnrolled)
                    // event(new PaymentCompleted($payment));
                    // event(new UserEnrolled($payment->user, $coursePurchase->course));
                    break;

                case 'payment.failed': // Simulated failure event
                    $payment->status = 'failed';
                    if ($simulatedGatewayReference) {
                        $payment->gateway_reference_id = $simulatedGatewayReference; // Store failure reference if any
                    }
                    $payment->save();
                    Log::info("Payment {$payment->id} processed (failed).");
                    // Optional: Dispatch an event (e.g., PaymentFailed)
                    // event(new PaymentFailed($payment));
                    break;

                default:
                    Log::info("Webhook received for unhandled event_type: {$eventType} for payment_id: {$paymentId}");
                    // Do not acknowledge if it is an event type we don't know how to handle or don't care about
                    // However, for many gateways, a 200 OK is still needed for any recognized event.
                    // Depending on gateway, might return 400 or 200 here.
                    DB::rollBack(); // Rollback if we started a transaction for an unhandled event.
                    return response()->json(['message' => 'Unhandled event type'], 200); // Or 400
            }

            DB::commit();
            return response()->json(['message' => 'Webhook processed successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing webhook for payment_id {$paymentId}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Return 500 to signal to the gateway that processing failed and it might need to retry (if gateway supports it)
            return response()->json(['error' => 'Internal server error during webhook processing'], 500);
        }
    }
} 