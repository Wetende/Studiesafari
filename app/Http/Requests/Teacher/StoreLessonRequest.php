<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Lesson; // For lesson_type enum if defined as a const

final class StoreLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Policy check: User can add lessons to the parent section
        // return $this->user()->can('addLesson', $this->route('section'));
        return true; // Placeholder
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $lessonTypes = ['text', 'video', 'zoom', 'stream', 'quiz_link', 'assignment_link']; // As per plan

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'lesson_type' => ['required', Rule::in($lessonTypes)],
            'lesson_duration' => ['nullable', 'string', 'max:50'], // e.g., "10 min", "1 hour"
            'is_preview_allowed' => ['sometimes', 'boolean'],
            'unlock_date' => ['nullable', 'date', 'after_or_equal:today'],
            'unlock_after_purchase_days' => ['nullable', 'integer', 'min:0'],
            'lesson_start_datetime' => ['nullable', 'date', 'after_or_equal:today'], 
            // 'order' will be handled by controller
        ];

        // Type-specific rules
        switch ($this->input('lesson_type')) {
            case 'text':
                $rules['short_description'] = ['nullable', 'string']; // Rich text
                $rules['content'] = ['required', 'string']; // Rich text
                break;
            case 'video':
                $rules['video_url'] = ['required', 'url_if_not_embed', 'string']; // Custom rule 'url_if_not_embed' or just string if embed code is text
                $rules['short_description'] = ['nullable', 'string']; // Rich text
                $rules['supplementary_content'] = ['nullable', 'string']; // Rich text
                break;
            case 'stream':
            case 'zoom':
                $rules['meeting_url'] = ['required', 'url'];
                $rules['meeting_id'] = ['nullable', 'string', 'max:255'];
                $rules['meeting_password'] = ['nullable', 'string', 'max:255'];
                $rules['start_time'] = ['required', 'date', 'after_or_equal:today'];
                $rules['short_description'] = ['nullable', 'string']; // Rich text
                $rules['supplementary_content'] = ['nullable', 'string']; // Rich text
                break;
            case 'quiz_link':
                $rules['linked_quiz_id'] = ['required', 'integer', Rule::exists('quizzes', 'id')->where(function ($query) {
                    // Optionally, ensure quiz belongs to the same course or is globally available
                    // For now, just exists in quizzes table
                })];
                break;
            case 'assignment_link':
                $rules['linked_assignment_id'] = ['required', 'integer', Rule::exists('assignments', 'id')->where(function ($query) {
                    // Optionally, ensure assignment belongs to the same course
                })];
                break;
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_preview_allowed' => $this->boolean('is_preview_allowed'),
        ]);
        
        // Potentially normalize video_url if it can be direct URL or embed code
        // if ($this->input('lesson_type') === 'video' && $this->input('video_source_type') === 'embed') { ... }
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'video_url.url_if_not_embed' => 'The video URL must be a valid URL or embed code.' // Example for custom rule
        ];
    }
} 