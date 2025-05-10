<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
// Eager loading models - ensure these exist and have relationships defined in Course model
use App\Models\User; // For Teacher & Authenticated User
use App\Models\Category;
use App\Models\Subject;
use App\Models\GradeLevel;
use App\Models\Enrollment; // For access status check
use App\Models\Payment;    // For access status check
use App\Models\Section;   // For curriculum
// Assuming Lesson, Quiz, Assignment models exist for curriculum items
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

final class CourseController extends Controller
{
    private const DEFAULT_PER_PAGE = 12;

    /**
     * Display a listing of the published courses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::query()->where('is_published', true)
            ->with([
                'teacher' => function ($query) {
                    $query->select('id', 'name', 'profile_photo_url'); // Select specific fields for teacher
                },
                'category:id,name,slug', // Select specific fields for category
                'subject:id,name,slug',   // Select specific fields for subject
                'gradeLevel:id,name,slug' // Select specific fields for gradeLevel
            ]);

        // Filtering
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }
        if ($request->has('grade_level_id')) {
            $query->where('grade_level_id', $request->input('grade_level_id'));
        }

        // Search
        if ($request->has('q')) {
            $searchTerm = $request->input('q');
            $query->where(function (Builder $subQuery) use ($searchTerm) {
                $subQuery->where('title', 'like', "%{$searchTerm}%")
                         ->orWhere('short_description', 'like', "%{$searchTerm}%")
                         ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Order by creation date or a specific order column if available
        $query->orderByDesc('created_at');

        $courses = $query->paginate($request->input('per_page', self::DEFAULT_PER_PAGE));

        // Data for filters (e.g., to populate dropdowns on the frontend)
        // These should ideally be fetched efficiently, possibly cached
        $filterData = [
            // Example: Fetch all relevant categories, subjects, grade levels
            // 'categories' => Category::orderBy('name')->get(['id', 'name']),
            // 'subjects' => Subject::orderBy('name')->get(['id', 'name']),
            // 'grade_levels' => GradeLevel::orderBy('name')->get(['id', 'name']),
        ];

        return response()->json([
            'courses' => $courses,
            // 'filters' => $filterData, // Uncomment and implement if filter data is needed
        ]);
    }

    /**
     * Display the specified course.
     * We'll assume a global scope or similar handles the 'is_published' check for route model binding,
     * or it's checked explicitly.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        // Route model binding handles fetching the course by its slug.
        // Explicit check for is_published, if not handled by a global scope.
        if (!$course->is_published) {
            return response()->json(['message' => 'Course not found or not published.'], 404);
        }
        
        $course->load([
            'teacher' => fn($q) => $q->select('id', 'name', 'profile_photo_url', 'bio'), // Added bio for teacher details
            'category:id,name,slug',
            'subject:id,name,slug',
            'gradeLevel:id,name,slug',
            'sections' => function ($query) {
                $query->orderBy('order')->with([
                    'lessons' => fn($q) => $q->orderBy('order')->select('id', 'section_id', 'title', 'content_type', 'duration', 'order'),
                    'quizzes' => fn($q) => $q->orderBy('order')->select('id', 'section_id', 'title', 'duration', 'order'), // Assuming Quiz has duration and order
                    'assignments' => fn($q) => $q->orderBy('order')->select('id', 'section_id', 'title', 'order') // Assuming Assignment has order, add duration if applicable
                ]);
            }
        ]);

        $accessStatus = 'guest';
        $user = Auth::user();

        if ($user) {
            // Check for active enrollment
            $isEnrolled = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->exists();

            if ($isEnrolled) {
                $accessStatus = 'enrolled';
            } else {
                // Check for pending purchase
                $hasPendingPurchase = Payment::where('user_id', $user->id)
                    ->where('payable_id', $course->id)
                    ->where('payable_type', Course::class)
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPendingPurchase) {
                    $accessStatus = 'pending_purchase';
                } else {
                    // Determine if course is free (assuming isFree() method on Course model)
                    // public function isFree(): bool { return $this->price <= 0 && is_null($this->required_subscription_tier_id); }
                    $isCourseFree = $course->price <= 0 && is_null($course->required_subscription_tier_id); // Simplified for now

                    if ($isCourseFree) {
                         // For free courses, if not enrolled, they can directly enroll (handled by different mechanism usually)
                         // For now, this state implies they can access/enroll without payment/subscription check.
                         // Actual enrollment for free courses might be a separate action.
                         // Let's assume 'can_enroll_free' if it is free and they are not yet enrolled.
                         $accessStatus = 'can_enroll_free'; 
                    }
                    // If not free, check purchase/subscription options
                    $canPurchase = $course->price > 0;
                    $canSubscribe = false;

                    if ($course->required_subscription_tier_id) {
                        $activeSubscription = $user->activeSubscription()->first(); // Assuming activeSubscription() relation returns the UserSubscription model
                        if ($activeSubscription && $activeSubscription->subscriptionTier) {
                            // Assuming SubscriptionTier has a 'level' attribute and Course has requiredSubscriptionTier relationship
                            if ($activeSubscription->subscriptionTier->level >= $course->requiredSubscriptionTier->level) {
                                $canSubscribe = true;
                            }
                        }
                    }

                    if ($canSubscribe && $canPurchase) {
                        $accessStatus = 'can_subscribe_or_purchase';
                    } elseif ($canSubscribe) {
                        $accessStatus = 'can_subscribe';
                    } elseif ($canPurchase) {
                        $accessStatus = 'can_purchase';
                    } else if (!$isCourseFree) { // If not free and no other option
                        $accessStatus = 'requires_higher_tier_or_unavailable'; // Or more specific message
                    }
                }
            }
        }

        // Prepare curriculum outline more cleanly
        $curriculumOutline = $course->sections->map(function ($section) {
            return [
                'id' => $section->id,
                'title' => $section->title,
                'order' => $section->order,
                'items' => collect($section->lessons)->map(fn($item) => ['id' => $item->id, 'title' => $item->title, 'type' => 'lesson', 'duration' => $item->duration, 'order' => $item->order])
                    ->merge($section->quizzes->map(fn($item) => ['id' => $item->id, 'title' => $item->title, 'type' => 'quiz', 'duration' => $item->duration, 'order' => $item->order]))
                    ->merge($section->assignments->map(fn($item) => ['id' => $item->id, 'title' => $item->title, 'type' => 'assignment', 'duration' => null, 'order' => $item->order])) // Assuming assignment duration not primary for outline
                    ->sortBy('order')->values()
            ];
        });

        return response()->json([
            'course' => $course->toArray(), // Convert to array to control output
            'curriculum_outline' => $curriculumOutline,
            'access_status' => $accessStatus
        ]);
    }
} 