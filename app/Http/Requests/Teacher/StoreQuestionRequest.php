<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

final class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Logic to authorize the user can create a question in this quiz
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
        $rules = [
            'text' => 'required|string',
            'question_type' => 'required|string|in:single_choice,multiple_choice,true_false,matching,image_matching,fill_in_the_gap,keywords',
            'points' => 'nullable|integer|min:1',
            'hint' => 'nullable|string',
            'explanation' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // 5MB max
            'add_to_my_library' => 'boolean',
            'subject_topic_id' => 'nullable|exists:subject_topics,id',
        ];
        
        // Add validation rules specific to question type
        $questionType = $this->input('question_type');
        
        if (in_array($questionType, ['single_choice', 'multiple_choice', 'true_false'])) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'nullable|string'; // Allow empty for image-only options
            $rules['is_correct'] = $questionType === 'single_choice' || $questionType === 'true_false'
                ? 'required|array|size:1'
                : 'required|array|min:1';
            $rules['is_correct.*'] = 'integer|min:0';
            $rules['option_images.*'] = 'nullable|image|max:5120';
        }
        
        // Additional rules for other question types can be added as needed
        
        return $rules;
    }
} 