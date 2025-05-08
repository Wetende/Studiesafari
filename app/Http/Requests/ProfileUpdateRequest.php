<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            
            // Student profile fields
            'student_profile.date_of_birth' => ['nullable', 'date'],
            'student_profile.grade_level_id' => ['nullable', 'exists:grade_levels,id'],
            'student_profile.school_name' => ['nullable', 'string', 'max:255'],
            'student_profile.learning_interests' => ['nullable', 'array'],
            
            // Teacher profile fields
            'teacher_profile.bio' => ['nullable', 'string', 'max:1000'],
            'teacher_profile.qualifications' => ['nullable', 'string', 'max:1000'],
            'teacher_profile.school_affiliation' => ['nullable', 'string', 'max:255'],
            'teacher_profile.position' => ['nullable', 'string', 'max:255'],
            'teacher_profile.hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'teacher_profile.available_for_tutoring' => ['nullable', 'boolean'],
            
            // Parent profile fields
            'parent_profile.occupation' => ['nullable', 'string', 'max:255'],
            'parent_profile.relationship_to_student' => ['nullable', 'string', 'max:255'],
            'parent_profile.notification_preferences' => ['nullable', 'array'],
        ];
    }
}
