<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
final class SubjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'icon_path' => null,
            'color_code' => fake()->hexColor(),
            'is_active' => true,
            'position' => fake()->numberBetween(1, 20),
            'curriculum_code' => fake()->optional(0.5)->regexify('[A-Z][0-9]'),
        ];
    }

    /**
     * Indicate that the subject is inactive.
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 