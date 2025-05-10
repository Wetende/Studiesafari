<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

final class StoreQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Logic to authorize the user can create a quiz in this section
        // For now, return true; implement proper authorization later
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:1', // in minutes
            'randomize_questions' => 'boolean',
            'show_correct_answer' => 'boolean',
            'pass_mark' => 'nullable|integer|min:0|max:100', // percentage
            'retake_penalty_percent' => 'nullable|integer|min:0|max:100',
            'style' => 'nullable|string|in:standard,exam,survey',
            'subject_id' => 'nullable|exists:subjects,id',
        ];
    }
} 