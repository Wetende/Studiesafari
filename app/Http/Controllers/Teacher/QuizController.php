<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Http\Requests\Teacher\StoreQuizRequest;
use App\Http\Requests\Teacher\UpdateQuizRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class QuizController extends Controller
{
    /**
     * Show the form for creating a new quiz.
     */
    public function create(Course $course, CourseSection $section): View
    {
        $this->authorize('update', $section); // Policy: can add quiz to section
        
        $quiz = new Quiz();

        return view('teacher.quizzes.create', compact('course', 'section', 'quiz'));
    }

    /**
     * Store a newly created quiz in storage.
     */
    public function store(StoreQuizRequest $request, Course $course, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $section); // Policy: can add quiz to section
        
        $validated = $request->validated();
        
        $maxOrder = $section->quizzes()->max('order') ?? 0;
        
        $quizData = [
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'randomize_questions' => $validated['randomize_questions'] ?? false,
            'show_correct_answer' => $validated['show_correct_answer'] ?? false,
            'pass_mark' => $validated['pass_mark'] ?? null,
            'retake_penalty_percent' => $validated['retake_penalty_percent'] ?? 0,
            'style' => $validated['style'] ?? 'standard',
            'order' => $maxOrder + 1,
            'subject_id' => $validated['subject_id'] ?? null,
        ];

        $quiz = $section->quizzes()->create($quizData);

        return redirect()->route('teacher.courses.quizzes.edit', [$course, $section, $quiz])
            ->with('success', 'Quiz created successfully. Now add some questions!');
    }

    /**
     * Display the specified quiz (preview or detailed view).
     */
    public function show(Course $course, CourseSection $section, Quiz $quiz): View
    {
        $this->authorize('view', $quiz);
        
        $questions = $quiz->questions()->with('options')->orderBy('order')->get();
        
        return view('teacher.quizzes.show', compact('course', 'section', 'quiz', 'questions'));
    }

    /**
     * Show the form for editing the specified quiz.
     */
    public function edit(Course $course, CourseSection $section, Quiz $quiz): View
    {
        $this->authorize('update', $quiz);
        
        $questions = $quiz->questions()->with('options')->orderBy('order')->get();
        
        return view('teacher.quizzes.edit', compact('course', 'section', 'quiz', 'questions'));
    }

    /**
     * Update the specified quiz in storage.
     */
    public function update(UpdateQuizRequest $request, Course $course, CourseSection $section, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);
        
        $validated = $request->validated();
        
        $quizData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'randomize_questions' => $validated['randomize_questions'] ?? false,
            'show_correct_answer' => $validated['show_correct_answer'] ?? false,
            'pass_mark' => $validated['pass_mark'] ?? null,
            'retake_penalty_percent' => $validated['retake_penalty_percent'] ?? 0,
            'style' => $validated['style'] ?? 'standard',
            'subject_id' => $validated['subject_id'] ?? null,
        ];

        $quiz->update($quizData);

        return redirect()->route('teacher.courses.quizzes.edit', [$course, $section, $quiz])
            ->with('success', 'Quiz updated successfully.');
    }

    /**
     * Remove the specified quiz from storage.
     */
    public function destroy(Course $course, CourseSection $section, Quiz $quiz): RedirectResponse
    {
        $this->authorize('delete', $quiz);
        
        $quiz->delete();

        return redirect()->route('teacher.courses.curriculum', $course)
            ->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Reorder the quiz within its section.
     */
    public function reorder(Request $request, Course $course, CourseSection $section): RedirectResponse
    {
        $this->authorize('update', $section);
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:quizzes,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $quiz = Quiz::findOrFail($item['id']);
            $quiz->update(['order' => $item['order']]);
        }

        return back()->with('success', 'Quiz order updated successfully.');
    }
} 