<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
final class LessonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lesson::class;

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
            'course_section_id' => $section->id,
            'title' => fake()->sentence(),
            'content_type' => 'text',
            'content' => fake()->paragraphs(3, true),
            'order' => fake()->numberBetween(1, 20),
            'is_free' => fake()->boolean(20),
        ];
    }

    /**
     * Create a text-based lesson.
     */
    public function text(): self
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'text',
            'content' => fake()->paragraphs(3, true),
        ]);
    }

    /**
     * Create a video embed lesson.
     */
    public function videoEmbed(): self
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'video_embed',
            'content' => 'https://www.youtube.com/watch?v=' . fake()->regexify('[A-Za-z0-9_-]{11}'),
        ]);
    }

    /**
     * Create a live session lesson.
     */
    public function liveSession(): self
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'live_session_link',
            'content' => 'https://zoom.us/j/' . fake()->numberBetween(10000000000, 99999999999),
        ]);
    }

    /**
     * Create a PDF lesson.
     */
    public function pdf(): self
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'pdf',
            'content' => 'lessons/pdfs/test-pdf-' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Create an image lesson.
     */
    public function image(): self
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'image',
            'content' => 'lessons/images/test-image-' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * Create a free preview lesson.
     */
    public function free(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_free' => true,
        ]);
    }

    /**
     * Set a specific order for the lesson.
     */
    public function withOrder(int $order): self
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }

    /**
     * Place the lesson in a specific section.
     */
    public function inSection(CourseSection $section): self
    {
        return $this->state(fn (array $attributes) => [
            'course_section_id' => $section->id,
            'course_id' => $section->course_id,
        ]);
    }
} 