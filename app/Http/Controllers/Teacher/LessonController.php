<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Http\Requests\Teacher\StoreLessonRequest;
use App\Http\Requests\Teacher\UpdateLessonRequest;
use Illuminate\Http\Request; // Temporarily use base Request
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage; // Added for file uploads
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class LessonController extends Controller
{
    /**
     * Show the form for creating a new lesson within a section.
     * This will likely present the "Select lesson type" modal first,
     * or a form that adapts based on the selected type.
     */
    public function create(Course $course, CourseSection $section, Request $request): View
    {
        $this->authorize('update', $section); // Policy: can add lesson to section
        // For now, this view might just be a placeholder or directly include the select type modal trigger.
        // Or, it could be part of the curriculum view, handled by JS.
        // Let's assume it returns a view that hosts the lesson creation UI elements.
        $lessonType = $request->query('lesson_type', 'text'); // Default to text or validate it exists in allowed types
        
        // Validate lessonType against allowed types if necessary
        $allowedLessonTypes = ['text', 'video', 'zoom', 'stream', 'quiz_link', 'assignment_link'];
        if (!in_array($lessonType, $allowedLessonTypes)) {
            // Handle invalid type, e.g., redirect back with error or default to 'text'
            abort(400, 'Invalid lesson type specified.');
        }

        $lesson = new Lesson(['lesson_type' => $lessonType]); // Create a new lesson instance with the type for the form

        // For quiz_link and assignment_link, you might want to load available quizzes/assignments for selection
        $availableQuizzes = [];
        $availableAssignments = [];
        if ($lessonType === 'quiz_link') {
            $availableQuizzes = $course->quizzes()->orderBy('title')->get(); // Or quizzes not yet linked in this course
        }
        if ($lessonType === 'assignment_link') {
            $availableAssignments = $course->assignments()->orderBy('title')->get(); // Or assignments not yet linked
        }

        return view('teacher.lessons.create', compact(
            'course', 
            'section', 
            'lesson', // Pass the new lesson with type
            'lessonType', 
            'availableQuizzes', 
            'availableAssignments'
        ));
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(StoreLessonRequest $request, Course $course, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $section); // Policy: can add lesson to section
        $validated = $request->validated();
        
        $maxOrder = $section->lessons()->max('order') ?? 0;
        
        $lessonData = [
            'course_id' => $course->id, // Ensure course_id is set
            'title' => $validated['title'],
            'lesson_type' => $validated['lesson_type'], // From hidden input or validated
            'order' => $maxOrder + 1,
            'lesson_duration' => $validated['lesson_duration'] ?? null,
            'is_preview_allowed' => $validated['is_preview_allowed'] ?? false,
            'is_published' => $validated['is_published'] ?? false, // Assuming a common field for publish status
            'unlock_date' => $validated['unlock_date'] ?? null,
            'unlock_after_purchase_days' => $validated['unlock_after_purchase_days'] ?? null,
            'short_description' => $validated['short_description'] ?? null, // Common optional field
            // user_id (author) could be set here, e.g., auth()->id()
            // 'user_id' => auth()->id(), 
        ];

        // Handle type-specific fields
        switch ($validated['lesson_type']) {
            case 'text':
                $lessonData['content'] = $validated['content'];
                break;
            case 'video':
                $lessonData['video_url'] = $validated['video_url'] ?? null;
                $lessonData['video_source'] = $validated['video_source'];
                $lessonData['video_embed_code'] = $validated['video_embed_code'] ?? null;
                $lessonData['enable_p_in_p'] = $validated['enable_p_in_p'] ?? false;
                $lessonData['enable_download'] = $validated['enable_download'] ?? false;
                $lessonData['content'] = $validated['content'] ?? null; // Video description/notes

                if ($validated['video_source'] === 'html5' && $request->hasFile('video_upload')) {
                    // Store new video
                    $path = $request->file('video_upload')->store('public/courses/' . $course->id . '/lessons/videos');
                    $lessonData['video_upload_path'] = Storage::url($path); // Store public URL or just path based on needs
                } elseif ($validated['video_source'] !== 'html5') {
                    $lessonData['video_upload_path'] = null; // Clear path if not html5
                }
                break;
            case 'stream': // Combined zoom/stream into 'stream' type
                $lessonData['stream_url'] = $validated['stream_url'];
                $lessonData['stream_password'] = $validated['stream_password'] ?? null;
                $lessonData['stream_start_time'] = $validated['stream_start_time'];
                $lessonData['stream_details'] = $validated['stream_details'] ?? null;
                $lessonData['is_recorded'] = $validated['is_recorded'] ?? false;
                $lessonData['recording_url'] = $validated['recording_url'] ?? null;
                break;
            case 'quiz_link':
                $lessonData['quiz_id'] = $validated['quiz_id'];
                $lessonData['instructions'] = $validated['instructions'] ?? null;
                // Ensure other content fields are null/default for linked types if model doesn't auto-handle
                $lessonData['content'] = null; 
                $lessonData['video_url'] = null;
                // ... etc. for other types to avoid carrying over data if type somehow changed post-validation (should not happen with good FE)
                break;
            case 'assignment_link':
                $lessonData['assignment_id'] = $validated['assignment_id'];
                $lessonData['instructions'] = $validated['instructions'] ?? null;
                $lessonData['content'] = null;
                $lessonData['video_url'] = null;
                break;
        }

        $section->lessons()->create($lessonData);

        return redirect()->route('teacher.courses.curriculum', $course)->with('success', 'Lesson created successfully.');
    }

    /**
     * Display the specified lesson (perhaps for a preview or direct linking, though typically edited in context).
     */
    public function show(Course $course, CourseSection $section, Lesson $lesson): View
    {
        $this->authorize('view', $lesson);
        return view('teacher.lessons.show', compact('course', 'section', 'lesson'));
    }

    /**
     * Show the form for editing the specified lesson.
     */
    public function edit(Course $course, CourseSection $section, Lesson $lesson): View
    {
        $this->authorize('update', $lesson);
        $lessonType = $lesson->lesson_type;

        $availableQuizzes = [];
        $availableAssignments = [];
        if ($lessonType === 'quiz_link') {
            $availableQuizzes = $course->quizzes()->orderBy('title')->get();
        }
        if ($lessonType === 'assignment_link') {
            $availableAssignments = $course->assignments()->orderBy('title')->get();
        }

        return view('teacher.lessons.edit', compact(
            'course', 
            'section', 
            'lesson',
            'lessonType', 
            'availableQuizzes', 
            'availableAssignments'
        ));
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(UpdateLessonRequest $request, Course $course, CourseSection $section, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);
        $validated = $request->validated();

        // Base data - lesson_type is not changed on update as per UpdateLessonRequest logic
        $updateData = [
            'title' => $validated['title'],
            // 'lesson_type' is not in $validated explicitly by design if not changeable, but good to have for consistency
            // 'lesson_type' => $lesson->lesson_type, // Retain existing type
            'lesson_duration' => $validated['lesson_duration'] ?? null,
            'is_preview_allowed' => $validated['is_preview_allowed'] ?? false,
            'is_published' => $validated['is_published'] ?? $lesson->is_published, // Keep old if not submitted
            'unlock_date' => $validated['unlock_date'] ?? null,
            'unlock_after_purchase_days' => $validated['unlock_after_purchase_days'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
        ];

        // Handle type-specific fields based on existing $lesson->lesson_type
        switch ($lesson->lesson_type->value) { // Access enum value for switch
            case 'text':
                $updateData['content'] = $validated['content'] ?? $lesson->content;
                break;
            case 'video':
                $updateData['video_url'] = $validated['video_url'] ?? null;
                $updateData['video_source'] = $validated['video_source'] ?? $lesson->video_source;
                $updateData['video_embed_code'] = $validated['video_embed_code'] ?? null;
                $updateData['enable_p_in_p'] = $validated['enable_p_in_p'] ?? false;
                $updateData['enable_download'] = $validated['enable_download'] ?? false;
                $updateData['content'] = $validated['content'] ?? $lesson->content; // Video description/notes

                if (($validated['video_source'] ?? $lesson->video_source) === 'html5') {
                    if ($request->hasFile('video_upload')) {
                        // Delete old video if exists
                        if ($lesson->video_upload_path) {
                            Storage::delete(str_replace(Storage::url(''), '', $lesson->video_upload_path)); // Convert URL to path for deletion
                        }
                        // Store new video
                        $path = $request->file('video_upload')->store('public/courses/' . $course->id . '/lessons/videos');
                        $updateData['video_upload_path'] = Storage::url($path);
                    } // else keep existing video_upload_path if no new file
                } else {
                    // If source changed from html5, delete old file and clear path
                    if ($lesson->video_source === 'html5' && $lesson->video_upload_path) {
                        Storage::delete(str_replace(Storage::url(''), '', $lesson->video_upload_path));
                    }
                    $updateData['video_upload_path'] = null;
                }
                break;
            case 'stream':
                $updateData['stream_url'] = $validated['stream_url'] ?? $lesson->stream_url;
                $updateData['stream_password'] = $validated['stream_password'] ?? null;
                $updateData['stream_start_time'] = $validated['stream_start_time'] ?? $lesson->stream_start_time;
                $updateData['stream_details'] = $validated['stream_details'] ?? null;
                $updateData['is_recorded'] = $validated['is_recorded'] ?? false;
                $updateData['recording_url'] = $validated['recording_url'] ?? null;
                break;
            case 'quiz_link':
                $updateData['quiz_id'] = $validated['quiz_id'] ?? $lesson->quiz_id;
                $updateData['instructions'] = $validated['instructions'] ?? null;
                break;
            case 'assignment_link':
                $updateData['assignment_id'] = $validated['assignment_id'] ?? $lesson->assignment_id;
                $updateData['instructions'] = $validated['instructions'] ?? null;
                break;
        }
        
        $lesson->update($updateData);

        return redirect()->route('teacher.courses.curriculum', $course)->with('success', 'Lesson updated successfully.');
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy(Course $course, CourseSection $section, Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        // If video lesson with an uploaded file, delete the file
        if ($lesson->lesson_type->value === 'video' && 
            ($lesson->video_source === 'html5' || $lesson->video_source === 'upload') && // Assuming 'html5' means uploaded
            $lesson->video_upload_path) {
            // Convert URL to storage path if necessary. Assuming video_upload_path stores a relative path or a full URL that needs parsing.
            // If video_upload_path stores a path like 'public/courses/.../video.mp4'
            $filePath = str_replace(Storage::url(''), '', $lesson->video_upload_path); // Basic way to try and get relative path from URL
            if (Storage::exists($filePath)) {
                 Storage::delete($filePath);
            } else {
                // Fallback for just path if it's not a full URL in the DB
                 $possiblePath = 'public/' . $lesson->video_upload_path; // Example if it's just 'courses/../video.mp4'
                 if (strpos($lesson->video_upload_path, 'courses/') === 0 && Storage::exists('public/' . $lesson->video_upload_path)) {
                    Storage::delete('public/' . $lesson->video_upload_path);
                 } else if (Storage::exists($lesson->video_upload_path)) { // If it's already a relative path from storage root like `courses/...` (not public/courses/...)
                    Storage::delete($lesson->video_upload_path);
                 }
            }
        }

        $lesson->delete(); // Assumes soft delete is set up on the Lesson model

        // TODO: Optionally, re-order remaining lessons in the section if needed, though usually not done on delete.

        return redirect()->route('teacher.courses.curriculum', $course)->with('success', 'Lesson deleted successfully.');
    }

    // TODO: Methods for managing LessonAttachments and Lesson Q&A will be added later in this subphase.
} 