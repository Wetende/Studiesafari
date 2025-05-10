<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\LessonType;
use App\Models\Assignment;

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
        'course_section_id',
        'title',
        'slug',
        'lesson_type',
        'order',
        'is_published',
        'is_preview_allowed',
        'unlock_date',
        'unlock_after_purchase_days',
        'short_description',
        'content',
        'lesson_duration',
        'video_url',
        'video_source',
        'video_upload_path',
        'video_embed_code',
        'enable_p_in_p',
        'enable_download',
        'stream_url',
        'stream_password',
        'stream_start_time',
        'stream_details',
        'is_recorded',
        'recording_url',
        'quiz_id',
        'assignment_id',
        'instructions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lesson_type' => LessonType::class,
        'order' => 'integer',
        'is_published' => 'boolean',
        'is_preview_allowed' => 'boolean',
        'unlock_date' => 'datetime',
        'lesson_duration' => 'integer',
        'enable_p_in_p' => 'boolean',
        'enable_download' => 'boolean',
        'stream_start_time' => 'datetime',
        'is_recorded' => 'boolean',
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
            ->where('order', '<', $this->order)
            ->where('is_published', true)
            ->orderBy('order', 'desc')
            ->first();
    }

    /**
     * Get the next lesson in the course.
     */
    public function getNextLesson()
    {
        return $this->course->lessons()
            ->where('order', '>', $this->order)
            ->where('is_published', true)
            ->orderBy('order')
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
        return $query;
    }

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    public function linkedQuiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function linkedAssignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }
}
