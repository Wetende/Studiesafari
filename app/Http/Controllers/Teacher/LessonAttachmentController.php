<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Http\Requests\Teacher\StoreLessonAttachmentRequest;
use App\Http\Requests\Teacher\UpdateLessonAttachmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

final class LessonAttachmentController extends Controller
{
    public function __construct()
    {
        // Add middleware for authorization if needed, e.g., for course ownership or teacher role
        // $this->middleware('can:update,course'); // Example, apply to relevant methods
    }

    public function index(Course $course, Lesson $lesson): JsonResponse
    {
        // this->authorize('view', $lesson); // Policy check
        $attachments = $lesson->attachments()->orderBy('order')->get();
        return response()->json($attachments);
    }

    public function store(StoreLessonAttachmentRequest $request, Course $course, Lesson $lesson): JsonResponse
    {
        // $this->authorize('update', $lesson); // Policy check
        $validated = $request->validated();
        $file = $request->file('file');
        
        $originalName = $file->getClientOriginalName();
        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeFileName = str()->slug($fileName) . '_' . time() . '.' . $extension;
        
        // Path: courses/{course_id}/lessons/{lesson_id}/attachments/{safe_file_name}
        $path = $file->storeAs(
            "courses/{$course->id}/lessons/{$lesson->id}/attachments",
            $safeFileName,
            'public'
        );

        if (!$path) {
            return response()->json(['message' => 'Failed to upload file.'], 500);
        }

        $maxOrder = $lesson->attachments()->max('order') ?? 0;

        $attachment = $lesson->attachments()->create([
            'user_id' => Auth::id(),
            'file_name' => $originalName, // Store original name for display
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'title' => $validated['title'] ?? $fileName, // Use original filename (without ext) if title not provided
            'description' => $validated['description'] ?? null,
            'order' => $maxOrder + 1,
        ]);

        return response()->json($attachment, 201);
    }

    public function update(UpdateLessonAttachmentRequest $request, Course $course, Lesson $lesson, LessonAttachment $attachment): JsonResponse
    {
        // $this->authorize('update', $attachment); // Policy check
        if ($attachment->lesson_id !== $lesson->id || $lesson->course_id !== $course->id) {
            return response()->json(['message' => 'Attachment not found or does not belong to this lesson.'], 404);
        }

        $validated = $request->validated();
        $attachment->update($validated);

        return response()->json($attachment);
    }

    public function destroy(Course $course, Lesson $lesson, LessonAttachment $attachment): JsonResponse
    {
        // $this->authorize('delete', $attachment); // Policy check
        if ($attachment->lesson_id !== $lesson->id || $lesson->course_id !== $course->id) {
            return response()->json(['message' => 'Attachment not found or does not belong to this lesson.'], 404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(null, 204);
    }

    public function reorder(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        // $this->authorize('update', $lesson); // Policy check
        $request->validate([
            'attachments' => ['required', 'array'],
            'attachments.*' => ['required', 'integer', 'exists:lesson_attachments,id'],
        ]);

        foreach ($request->input('attachments') as $index => $attachmentId) {
            LessonAttachment::where('id', $attachmentId)
                ->where('lesson_id', $lesson->id) // Ensure attachment belongs to the lesson
                ->update(['order' => $index + 1]);
        }

        return response()->json(['message' => 'Attachments reordered successfully.']);
    }
} 