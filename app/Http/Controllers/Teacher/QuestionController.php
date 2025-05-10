<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Http\Requests\Teacher\StoreQuestionRequest;
use App\Http\Requests\Teacher\UpdateQuestionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class QuestionController extends Controller
{
    /**
     * Show the form for creating a new question within a quiz.
     */
    public function create(Course $course, CourseSection $section, Quiz $quiz, Request $request): View
    {
        // $this->authorize('update', $quiz); // Policy: can add question to quiz
        
        $question = new Question();
        $questionType = $request->query('question_type', 'single_choice');
        
        // Get subject topics for the subject of the quiz (if any)
        $subjectTopics = [];
        if ($quiz->subject_id) {
            $subjectTopics = \App\Models\SubjectTopic::where('subject_id', $quiz->subject_id)
                ->orderBy('name')
                ->get();
        }
        
        return view('teacher.questions.create', compact(
            'course', 
            'section', 
            'quiz', 
            'question', 
            'questionType',
            'subjectTopics'
        ));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(StoreQuestionRequest $request, Course $course, CourseSection $section, Quiz $quiz): RedirectResponse
    {
        // $this->authorize('update', $quiz); // Policy: can add question to quiz
        
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            $maxOrder = $quiz->questions()->max('order') ?? 0;
            
            $questionData = [
                'quiz_id' => $quiz->id,
                'text' => $validated['text'],
                'question_type' => $validated['question_type'],
                'points' => $validated['points'] ?? 1,
                'order' => $maxOrder + 1,
                'hint' => $validated['hint'] ?? null,
                'explanation' => $validated['explanation'] ?? null,
                'add_to_my_library' => $validated['add_to_my_library'] ?? false,
                'subject_topic_id' => $validated['subject_topic_id'] ?? null,
            ];
            
            // Handle image upload if present
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('public/quizzes/' . $quiz->id . '/questions');
                $questionData['image_path'] = \Illuminate\Support\Facades\Storage::url($path);
            }
            
            $question = $quiz->questions()->create($questionData);
            
            // Handle options based on question type
            if (in_array($validated['question_type'], ['single_choice', 'multiple_choice', 'true_false'])) {
                $this->processOptions($question, $validated, $request);
            }
            
            DB::commit();
            
            return redirect()->route('teacher.courses.quizzes.edit', [$course, $section, $quiz])
                ->with('success', 'Question added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->withErrors(['message' => 'Failed to create question: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Course $course, CourseSection $section, Quiz $quiz, Question $question): View
    {
        // $this->authorize('update', $question);
        
        $questionType = $question->question_type;
        $options = $question->options;
        
        // Get subject topics for the subject of the quiz (if any)
        $subjectTopics = [];
        if ($quiz->subject_id) {
            $subjectTopics = \App\Models\SubjectTopic::where('subject_id', $quiz->subject_id)
                ->orderBy('name')
                ->get();
        }
        
        return view('teacher.questions.edit', compact(
            'course', 
            'section', 
            'quiz', 
            'question', 
            'questionType',
            'options',
            'subjectTopics'
        ));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(UpdateQuestionRequest $request, Course $course, CourseSection $section, Quiz $quiz, Question $question): RedirectResponse
    {
        // $this->authorize('update', $question);
        
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            $questionData = [
                'text' => $validated['text'],
                // Question type is not typically changed after creation, but you could allow it with care
                'points' => $validated['points'] ?? 1,
                'hint' => $validated['hint'] ?? null,
                'explanation' => $validated['explanation'] ?? null,
                'add_to_my_library' => $validated['add_to_my_library'] ?? false,
                'subject_topic_id' => $validated['subject_topic_id'] ?? null,
            ];
            
            // Handle image upload if present
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($question->image_path) {
                    $oldPath = str_replace('/storage', 'public', $question->image_path);
                    \Illuminate\Support\Facades\Storage::delete($oldPath);
                }
                
                $path = $request->file('image')->store('public/quizzes/' . $quiz->id . '/questions');
                $questionData['image_path'] = \Illuminate\Support\Facades\Storage::url($path);
            } elseif ($validated['remove_image'] ?? false) {
                // Remove image if requested
                if ($question->image_path) {
                    $oldPath = str_replace('/storage', 'public', $question->image_path);
                    \Illuminate\Support\Facades\Storage::delete($oldPath);
                    $questionData['image_path'] = null;
                }
            }
            
            $question->update($questionData);
            
            // Handle options based on question type
            if (in_array($question->question_type, ['single_choice', 'multiple_choice', 'true_false'])) {
                // Delete existing options
                $question->options()->delete();
                // Create new options
                $this->processOptions($question, $validated, $request);
            }
            
            DB::commit();
            
            return redirect()->route('teacher.courses.quizzes.edit', [$course, $section, $quiz])
                ->with('success', 'Question updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->withErrors(['message' => 'Failed to update question: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Course $course, CourseSection $section, Quiz $quiz, Question $question): RedirectResponse
    {
        // $this->authorize('delete', $question);
        
        $question->delete();

        return redirect()->route('teacher.courses.quizzes.edit', [$course, $section, $quiz])
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Reorder questions within a quiz.
     */
    public function reorder(Request $request, Course $course, CourseSection $section, Quiz $quiz): RedirectResponse
    {
        // $this->authorize('update', $quiz);
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:questions,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $question = Question::findOrFail($item['id']);
            $question->update(['order' => $item['order']]);
        }

        return back()->with('success', 'Question order updated successfully.');
    }

    /**
     * Process and save question options from the request data.
     */
    private function processOptions(Question $question, array $validated, Request $request): void
    {
        $options = $validated['options'] ?? [];
        $isCorrect = $validated['is_correct'] ?? [];
        
        foreach ($options as $key => $text) {
            if (empty($text) && !$request->hasFile("option_images.$key")) {
                continue; // Skip empty options
            }
            
            $optionData = [
                'question_id' => $question->id,
                'text' => $text,
                'is_correct' => in_array($key, $isCorrect),
                'order' => $key,
            ];
            
            // Handle option image if present
            if ($request->hasFile("option_images.$key")) {
                $path = $request->file("option_images.$key")->store('public/quizzes/' . $question->quiz_id . '/options');
                $optionData['image_url'] = \Illuminate\Support\Facades\Storage::url($path);
            }
            
            QuestionOption::create($optionData);
        }
    }
} 