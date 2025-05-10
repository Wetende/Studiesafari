<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
final class QuizFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Quiz::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $section = CourseSection::factory()->create();
        
        return [
            'course_id' => $section->course_id,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'time_limit' => fake()->numberBetween(15, 120), // minutes
            'randomize_questions' => fake()->boolean(70),
            'show_correct_answer' => fake()->boolean(70),
            'passing_grade' => fake()->numberBetween(60, 90), // percentage
            'retake_penalty_percent' => fake()->randomFloat(2, 0, 10),
            'style' => null,
            'order' => fake()->numberBetween(1, 10),
            'subject_id' => null,
        ];
    }

    /**
     * Set a subject for the quiz.
     */
    public function withSubject(?Subject $subject = null): self
    {
        return $this->state(function (array $attributes) use ($subject) {
            return [
                'subject_id' => $subject ? $subject->id : Subject::factory(),
            ];
        });
    }

    /**
     * Specify a course for the quiz.
     */
    public function forCourse(Course $course): self
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $course->id,
        ]);
    }
} 