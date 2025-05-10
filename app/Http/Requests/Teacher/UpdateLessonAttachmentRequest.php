<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class UpdateLessonAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ensure the lesson attachment belongs to the lesson, course and user can update
        // Basic check: user is authenticated.
        // For example, Auth::user()->can('update', $this->route('attachment'));
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
} 