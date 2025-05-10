<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CourseFAQ extends Model
{
    use HasFactory;

    protected $table = 'course_faqs'; // Explicitly define table name

    protected $fillable = [
        'course_id',
        'title',
        'question',
        'answer',
        'order',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
