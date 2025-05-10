<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSubscriptionTierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Assuming admin users are authorized to create tiers
        return $this->user()->hasRole('admin'); // Adjust if your role check is different
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('subscription_tiers', 'name')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'level' => ['required', 'integer', 'min:0', Rule::unique('subscription_tiers', 'level')],
            'duration_days' => ['required', 'integer', 'min:0'],
            'max_courses' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'json'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'max_courses' => $this->max_courses == 0 ? null : $this->max_courses, // Treat 0 as null for unlimited
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'The subscription tier name has already been taken.',
            'level.unique' => 'The subscription tier level has already been assigned.',
        ];
    }
}
