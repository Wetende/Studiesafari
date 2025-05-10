<?php

declare(strict_types=1);

namespace App\Enums;

enum LessonType: string
{
    case TEXT = 'text';
    case VIDEO = 'video';
    case STREAM = 'stream'; // For generic live streams, could be Zoom, Google Meet, etc.
    case QUIZ_LINK = 'quiz_link';
    case ASSIGNMENT_LINK = 'assignment_link';

    public function SreadableName(): string
    {
        return match ($this) {
            self::TEXT => 'Text Content',
            self::VIDEO => 'Video',
            self::STREAM => 'Live Stream / Webinar',
            self::QUIZ_LINK => 'Link to Quiz',
            self::ASSIGNMENT_LINK => 'Link to Assignment',
        };
    }
} 