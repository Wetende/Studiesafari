<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use App\Models\Subject;
use App\Models\GradeLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(Request $request): View
    {
        // Start with a query for all courses
        $coursesQuery = Course::query();
        
        // Apply filters
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'published') {
                $coursesQuery->where('is_published', true);
            } elseif ($status === 'draft') {
                $coursesQuery->where('is_published', false);
            }
        }
        
        if ($request->filled('category')) {
            $coursesQuery->where('category_id', $request->input('category'));
        }
        
        if ($request->filled('subject')) {
            $coursesQuery->where('subject_id', $request->input('subject'));
        }
        
        if ($request->filled('teacher')) {
            $coursesQuery->where('user_id', $request->input('teacher'));
        }
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $coursesQuery->where(function($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['title', 'created_at', 'published_at', 'price', 'enrollment_count'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        $coursesQuery->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        
        // Eager load relationships for efficiency
        $coursesQuery->with(['category', 'subject', 'gradeLevel', 'user']);
        
        // Add statistics like enrollment count, rating, and revenue
        $coursesQuery->withCount('purchases as enrollment_count');
        
        // Paginate results
        $courses = $coursesQuery->paginate(15)->withQueryString();
        
        // Get data for filters
        $categories = Category::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = User::role('teacher')->orderBy('name')->get();
        
        return view('admin.courses.index', compact(
            'courses', 
            'categories', 
            'subjects', 
            'teachers'
        ));
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course): View
    {
        $course->load(['user', 'category', 'subject', 'gradeLevel']);
        $course->loadCount('purchases as enrollment_count');
        
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the course.
     */
    public function edit(Course $course): View
    {
        $course->load(['user', 'category', 'subject', 'gradeLevel']);
        
        $categories = Category::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();
        $teachers = User::role('teacher')->orderBy('name')->get();
        
        return view('admin.courses.edit', compact(
            'course', 
            'categories', 
            'subjects', 
            'gradeLevels', 
            'teachers'
        ));
    }

    /**
     * Update the course status (publish/unpublish).
     */
    public function updateStatus(Request $request, Course $course): RedirectResponse
    {
        $request->validate([
            'is_published' => 'required|boolean',
        ]);

        $wasPublished = $course->is_published;
        $isPublished = (bool) $request->input('is_published');

        // If publishing for the first time, set published_at
        if (!$wasPublished && $isPublished) {
            $course->published_at = now();
        }

        $course->is_published = $isPublished;
        $course->save();

        $message = $isPublished ? 'Course published successfully.' : 'Course unpublished successfully.';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Make a course featured/unfeatured.
     */
    public function toggleFeatured(Request $request, Course $course): RedirectResponse
    {
        $course->is_featured = !$course->is_featured;
        $course->save();

        $message = $course->is_featured 
            ? 'Course marked as featured.' 
            : 'Course removed from featured.';
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Make a course recommended/unrecommended.
     */
    public function toggleRecommended(Request $request, Course $course): RedirectResponse
    {
        $course->is_recommended = !$course->is_recommended;
        $course->save();

        $message = $course->is_recommended 
            ? 'Course marked as recommended.' 
            : 'Course removed from recommended.';
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course): RedirectResponse
    {
        // Soft delete the course
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }

    /**
     * Permanently remove the specified course.
     */
    public function forceDelete(Course $course): RedirectResponse
    {
        // If there are enrollments, prevent deletion
        if ($course->purchases()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot permanently delete a course with enrollments.');
        }

        // Delete thumbnail if exists
        if ($course->thumbnail_path && Storage::cloud()->exists($course->thumbnail_path)) {
            Storage::cloud()->delete($course->thumbnail_path);
        }

        // Force delete the course
        $course->forceDelete();

        return redirect()->route('admin.courses.index')->with('success', 'Course permanently deleted.');
    }

    /**
     * Restore a soft-deleted course.
     */
    public function restore(int $courseId): RedirectResponse
    {
        $course = Course::withTrashed()->findOrFail($courseId);
        $course->restore();
        
        return redirect()->route('admin.courses.index')->with('success', 'Course restored successfully.');
    }

    /**
     * Display a listing of trashed courses.
     */
    public function trash(Request $request): View
    {
        // Get soft-deleted courses with filters similar to index
        $coursesQuery = Course::onlyTrashed();
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $coursesQuery->where(function($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%");
            });
        }
        
        // Eager load relationships
        $coursesQuery->with(['category', 'subject', 'gradeLevel', 'user']);
        
        // Order by deleted_at by default (most recently deleted first)
        $coursesQuery->orderBy('deleted_at', 'desc');
        
        // Paginate results
        $courses = $coursesQuery->paginate(15)->withQueryString();
        
        return view('admin.courses.trash', compact('courses'));
    }
    
    /**
     * Display the curriculum for a course (admin oversight).
     */
    public function showCurriculum(Course $course): View
    {
        // Load course with its sections and content
        $course->load(['sections' => function ($query) {
            $query->orderBy('order')->with([
                'lessons' => function ($q) { $q->orderBy('order'); },
                'quizzes' => function ($q) { $q->orderBy('order'); },
                'assignments' => function ($q) { $q->orderBy('order'); }
            ]);
        }]);
        
        return view('admin.courses.curriculum', compact('course'));
    }
}
