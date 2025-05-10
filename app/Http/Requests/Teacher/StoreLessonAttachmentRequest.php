<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class StoreLessonAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ensure the lesson belongs to the course and the user can update the course/lesson
        // Basic check: user is authenticated. More specific authorization can be added.
        // For example, check if Auth::user()->can('update', $this->route('lesson'));
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:102400'], // Max 100MB, adjust as needed. Add mimes if specific types are required e.g. 'mimes:pdf,doc,docx,zip,jpg,png'
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
} 