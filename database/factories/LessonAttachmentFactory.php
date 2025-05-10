<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LessonAttachment>
 */
final class LessonAttachmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LessonAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['pdf', 'docx', 'pptx', 'xlsx', 'zip'];
        $fileType = fake()->randomElement($fileTypes);
        $fileName = 'attachment-' . fake()->uuid() . '.' . $fileType;
        
        return [
            'lesson_id' => Lesson::factory(),
            'title' => fake()->words(3, true),
            'file_path' => 'lessons/attachments/' . $fileName,
            'file_name' => $fileName,
            'file_size' => fake()->numberBetween(100, 10000), // KB
            'file_type' => 'application/' . $fileType,
            'description' => fake()->optional()->sentence(),
            'is_downloadable' => true,
            'order' => fake()->numberBetween(1, 5),
        ];
    }

    /**
     * Make the attachment a PDF file.
     */
    public function pdf(): self
    {
        $fileName = 'attachment-' . fake()->uuid() . '.pdf';
        return $this->state(fn (array $attributes) => [
            'file_path' => 'lessons/attachments/' . $fileName,
            'file_name' => $fileName,
            'file_type' => 'application/pdf',
        ]);
    }

    /**
     * Make the attachment a document (docx) file.
     */
    public function document(): self
    {
        $fileName = 'attachment-' . fake()->uuid() . '.docx';
        return $this->state(fn (array $attributes) => [
            'file_path' => 'lessons/attachments/' . $fileName,
            'file_name' => $fileName,
            'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Make the attachment a presentation (pptx) file.
     */
    public function presentation(): self
    {
        $fileName = 'attachment-' . fake()->uuid() . '.pptx';
        return $this->state(fn (array $attributes) => [
            'file_path' => 'lessons/attachments/' . $fileName,
            'file_name' => $fileName,
            'file_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);
    }

    /**
     * Specify the lesson for the attachment.
     */
    public function forLesson(Lesson $lesson): self
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => $lesson->id,
        ]);
    }

    /**
     * Make the attachment not downloadable.
     */
    public function notDownloadable(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_downloadable' => false,
        ]);
    }
} 