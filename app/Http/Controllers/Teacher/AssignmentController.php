<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Http\Requests\Teacher\StoreAssignmentRequest;
use App\Http\Requests\Teacher\UpdateAssignmentRequest;
use App\Http\Requests\Teacher\GradeSubmissionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AssignmentController extends Controller
{
    /**
     * Show the form for creating a new assignment.
     */
    public function create(Course $course, CourseSection $section): View
    {
        $this->authorize('update', $section); // Policy: can add assignment to section
        
        $assignment = new Assignment();
        
        return view('teacher.assignments.create', compact('course', 'section', 'assignment'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(StoreAssignmentRequest $request, Course $course, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $section); // Policy: can add assignment to section
        
        $validated = $request->validated();
        
        $maxOrder = $section->assignments()->max('order') ?? 0;
        
        $assignmentData = [
            'course_section_id' => $section->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'instructions' => $validated['instructions'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'points_possible' => $validated['points_possible'] ?? null,
            'allowed_submission_types' => $validated['allowed_submission_types'] ?? ['pdf', 'docx', 'zip'],
            'unlock_date' => $validated['unlock_date'] ?? null,
            'order' => $maxOrder + 1,
        ];
        
        $assignment = $section->assignments()->create($assignmentData);
        
        return redirect()->route('teacher.courses.curriculum', $course)
            ->with('success', 'Assignment created successfully.');
    }

    /**
     * Display the specified assignment.
     */
    public function show(Course $course, CourseSection $section, Assignment $assignment): View
    {
        $this->authorize('view', $assignment);
        
        return view('teacher.assignments.show', compact('course', 'section', 'assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(Course $course, CourseSection $section, Assignment $assignment): View
    {
        $this->authorize('update', $assignment);
        
        return view('teacher.assignments.edit', compact('course', 'section', 'assignment'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(UpdateAssignmentRequest $request, Course $course, CourseSection $section, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment);
        
        $validated = $request->validated();
        
        $assignmentData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'instructions' => $validated['instructions'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'points_possible' => $validated['points_possible'] ?? null,
            'allowed_submission_types' => $validated['allowed_submission_types'] ?? ['pdf', 'docx', 'zip'],
            'unlock_date' => $validated['unlock_date'] ?? null,
        ];
        
        $assignment->update($assignmentData);
        
        return redirect()->route('teacher.courses.assignments.edit', [$course, $section, $assignment])
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(Course $course, CourseSection $section, Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);
        
        $assignment->delete();
        
        return redirect()->route('teacher.courses.curriculum', $course)
            ->with('success', 'Assignment deleted successfully.');
    }

    /**
     * Reorder assignments within a section.
     */
    public function reorder(Request $request, Course $course, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $section);
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:assignments,id',
            'items.*.order' => 'required|integer|min:0',
        ]);
        
        foreach ($validated['items'] as $item) {
            $assignment = Assignment::findOrFail($item['id']);
            $assignment->update(['order' => $item['order']]);
        }
        
        return back()->with('success', 'Assignment order updated successfully.');
    }

    /**
     * View all submissions for an assignment.
     */
    public function submissions(Course $course, CourseSection $section, Assignment $assignment): View
    {
        $this->authorize('view', $assignment);
        
        $submissions = $assignment->submissions()
            ->with('user')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);
        
        return view('teacher.assignments.submissions.index', compact(
            'course',
            'section',
            'assignment',
            'submissions'
        ));
    }

    /**
     * View a specific submission.
     */
    public function viewSubmission(Course $course, CourseSection $section, Assignment $assignment, AssignmentSubmission $submission): View
    {
        $this->authorize('view', $submission);
        
        return view('teacher.assignments.submissions.show', compact(
            'course',
            'section',
            'assignment',
            'submission'
        ));
    }

    /**
     * Grade a submission.
     */
    public function gradeSubmission(GradeSubmissionRequest $request, Course $course, CourseSection $section, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        $this->authorize('grade', $submission);
        
        $validated = $request->validated();
        
        $submission->update([
            'grade' => $validated['grade'],
            'teacher_feedback' => $validated['teacher_feedback'] ?? null,
            'graded_at' => now(),
            'grading_teacher_id' => Auth::id(),
        ]);
        
        return redirect()->route('teacher.courses.assignments.submissions', [
            $course,
            $section,
            $assignment,
        ])->with('success', 'Submission graded successfully.');
    }
} 