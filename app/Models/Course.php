<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'short_description',
        'thumbnail_path',
        'price',
        'level',
        'language',
        'requirements',
        'what_you_will_learn',
        'tags',
        'duration_in_minutes',
        'is_featured',
        'is_published',
        'published_at',
        'subscription_required',
        'position',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'requirements' => 'array',
        'what_you_will_learn' => 'array',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'subscription_required' => 'boolean',
        'position' => 'integer',
        'duration_in_minutes' => 'integer',
    ];

    /**
     * Get the teacher who owns the course.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the lessons for the course.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Get the discussion forum for the course.
     */
    public function discussionForum(): HasOne
    {
        return $this->hasOne(DiscussionForum::class);
    }

    /**
     * Get the reviews for the course.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    /**
     * Get the purchases for the course.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(CoursePurchase::class);
    }

    /**
     * Get the average rating for the course.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get the total number of students enrolled in the course.
     */
    public function getTotalStudentsAttribute(): int
    {
        return $this->purchases()->count();
    }

    /**
     * Get the total duration of the course in minutes.
     */
    public function calculateTotalDuration(): int
    {
        return $this->lessons()->sum('duration_in_minutes');
    }

    /**
     * Get the published lessons for the course.
     */
    public function publishedLessons(): HasMany
    {
        return $this->lessons()->where('is_published', true)->orderBy('position');
    }

    /**
     * Scope a query to only include published courses.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include featured courses.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
