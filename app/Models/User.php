<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache; // For caching active subscription
use Illuminate\Support\Facades\Log;
use App\Models\Enrollment; // Added import

final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'profile_picture_path',
        'phone_number',
        'email_verified_at',
        'phone_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the courses created by the user as a teacher.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the user's course purchases.
     */
    public function coursePurchases(): HasMany
    {
        return $this->hasMany(CoursePurchase::class);
    }

    /**
     * Get the user's subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get the user's payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the user's lesson completions.
     */
    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    /**
     * Get the user's quiz attempts.
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get the user's forum topics.
     */
    public function forumTopics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    /**
     * Get the user's forum posts.
     */
    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    /**
     * Get the user's course reviews.
     */
    public function courseReviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    /**
     * Get the user's payment details (for teachers).
     */
    public function paymentDetails(): HasMany
    {
        return $this->hasMany(TeacherPaymentDetail::class);
    }

    /**
     * Get the user's payouts (for teachers).
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(TeacherPayout::class);
    }

    /**
     * Get the user's enrollments.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is a teacher.
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    /**
     * Check if the user is a student.
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    /**
     * Check if the user is a parent.
     */
    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    /**
     * Get the user's currently active subscription.
     * Uses Cache for performance within a single request lifecycle.
     */
    public function activeSubscription(): ?UserSubscription
    {
        return Cache::remember("user_{$this->id}_active_subscription", now()->addMinutes(1), function () {
            return $this->subscriptions()->currentlyActive()->latest('started_at')->first();
        });
    }

    /**
     * Check if the user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Get the tier of the user's currently active subscription.
     */
    public function activeSubscriptionTier(): ?SubscriptionTier
    {
        return $this->activeSubscription()?->tier;
    }
    
    /**
     * Count the number of currently active enrollments obtained via subscription.
     */
    public function countActiveSubscriptionEnrollments(): int
    {
        // Assuming an 'active' status on enrollments means status = 'active'
        return $this->enrollments()
                    ->where('access_type', 'subscription')
                    ->where('status', 'active') // Check enrollment status
                    ->count(); 
    }

    /**
     * Check if the user is allowed to enroll in more courses based on subscription limit.
     */
    public function isEnrollmentAllowedByMaxCoursesLimit(): bool
    {
        $subscription = $this->activeSubscription();
        
        if (!$subscription) {
            return false; // No active subscription
        }
        
        $maxCourses = $subscription->tier->max_courses;
        
        if ($maxCourses === null) {
            return true; // Unlimited courses allowed
        }
        
        $currentCount = $this->countActiveSubscriptionEnrollments();
        
        return $currentCount < $maxCourses;
    }

    /**
     * Check if the user can access a specific course via their subscription.
     */
    public function canAccessCourseViaSubscription(Course $course): bool
    {
        // If the course doesn't require a subscription, anyone can access
        if (!$course->subscription_required) {
            return true;
        }
        
        // User must have an active subscription
        $subscription = $this->activeSubscription();
        if (!$subscription) {
            return false;
        }
        
        // If the course has a specific tier requirement, check that
        if ($course->required_subscription_tier_id) {
            $userTierId = $subscription->subscription_tier_id;
            $requiredTierId = $course->required_subscription_tier_id;
            
            // The user's tier must be at least the required tier
            // Assuming higher tier IDs are more premium - this logic might need adjustment
            return $userTierId >= $requiredTierId;
        }
        
        // Course requires subscription but no specific tier
        return true;
    }
}
