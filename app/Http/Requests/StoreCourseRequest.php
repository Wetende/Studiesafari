<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization logic will be handled by policies in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('courses', 'slug')],
            'description' => ['required', 'string'],
            'short_description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'grade_level_id' => ['nullable', 'integer', Rule::exists('grade_levels', 'id')],
            'thumbnail_path' => ['nullable', 'string', 'max:255'], // Or image validation if handling uploads here
            'language' => ['nullable', 'string', 'max:50'],
            'what_you_will_learn' => ['nullable', 'array'],
            'what_you_will_learn.*' => ['nullable', 'string'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'required_subscription_tier_id' => ['nullable', 'integer', Rule::exists('subscription_tiers', 'id')],
            'is_published' => ['sometimes', 'boolean'],
            // Drip Tab fields (example)
            // 'course_unlock_date' => ['nullable', 'date'],
            // 'course_unlock_after_days' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
