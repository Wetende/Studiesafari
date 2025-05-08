<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'description',
        'content',
        'video_url',
        'duration_in_minutes',
        'is_free',
        'is_published',
        'position',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_in_minutes' => 'integer',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Get the course that owns the lesson.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the quiz for the lesson.
     */
    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    /**
     * Get the attachments for the lesson.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(LessonAttachment::class);
    }

    /**
     * Get the completions for the lesson.
     */
    public function completions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    /**
     * Check if the lesson is completed by a specific user.
     */
    public function isCompletedByUser(int $userId): bool
    {
        return $this->completions()->where('user_id', $userId)->exists();
    }

    /**
     * Get the previous lesson in the course.
     */
    public function getPreviousLesson()
    {
        return $this->course->lessons()
            ->where('position', '<', $this->position)
            ->where('is_published', true)
            ->orderBy('position', 'desc')
            ->first();
    }

    /**
     * Get the next lesson in the course.
     */
    public function getNextLesson()
    {
        return $this->course->lessons()
            ->where('position', '>', $this->position)
            ->where('is_published', true)
            ->orderBy('position')
            ->first();
    }

    /**
     * Scope a query to only include published lessons.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include free lessons.
     */
    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }
}
