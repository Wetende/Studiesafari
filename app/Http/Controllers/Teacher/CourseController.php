<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Requests\Teacher\StoreCourseSectionRequest;
use App\Http\Requests\Teacher\UpdateCourseSectionRequest;
use App\Models\CourseSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Start with a query for courses created by the current teacher
        $coursesQuery = Course::where('user_id', $user->id);
        
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
        $allowedSortFields = ['title', 'created_at', 'published_at', 'price'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        $coursesQuery->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        
        // Eager load relationships for efficiency
        $coursesQuery->with(['category', 'subject', 'gradeLevel']);
        
        // Add statistics like enrollment count and rating
        $coursesQuery->withCount('purchases as enrollment_count');
        
        // Paginate results
        $courses = $coursesQuery->paginate(10)->withQueryString();
        
        // Get data for filters
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return view('teacher.courses.index', compact('courses', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): View
    {
        // Example: return view('teacher.courses.show', compact('course'));
        // For now, let's assume this redirects or shows a general overview
        // and the curriculum tab is a specific sub-view or part of the edit view.
        return view('teacher.courses.edit.settings', compact('course')); // Placeholder
    }

    /**
     * Show the form for editing the specified resource.
     * This might be the main entry for the tabbed interface.
     */
    public function edit(Course $course): View
    {
        // Default to curriculum tab, or load based on a query param
        return $this->showCurriculumTab($course);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        //
    }

    /**
     * Update the course status (publish/unpublish).
     */
    public function updateStatus(Request $request, Course $course): RedirectResponse
    {
        // Ensure the teacher owns this course
        if (Auth::id() !== $course->user_id) {
            return redirect()->back()->with('error', 'You are not authorized to update this course.');
        }

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
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        // Ensure the teacher owns this course
        if (Auth::id() !== $course->user_id) {
            return redirect()->back()->with('error', 'You are not authorized to delete this course.');
        }

        // Soft delete the course
        $course->delete();

        return redirect()->route('teacher.courses.index')->with('success', 'Course deleted successfully.');
    }

    // Methods for Subphase 3.3: Section & Content Management

    /**
     * Display the curriculum tab for a course.
     */
    public function showCurriculumTab(Course $course): View
    {
        // Ensure authorization: user can manage this course
        // $this->authorize('update', $course);

        $course->load(['sections' => function ($query) {
            $query->orderBy('order')->with(['lessons', 'quizzes', 'assignments']);
        }]);
        return view('teacher.courses.edit.curriculum', compact('course'));
    }

    /**
     * Store a newly created course section.
     */
    public function storeSection(StoreCourseSectionRequest $request, Course $course): RedirectResponse
    {
        // $this->authorize('update', $course); // Or a more specific 'addSection' policy

        $validated = $request->validated();
        $maxOrder = $course->sections()->max('order');
        
        $course->sections()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
            'unlock_date' => $validated['unlock_date'] ?? null,
            'unlock_after_days' => $validated['unlock_after_days'] ?? null,
            'order' => $maxOrder + 1,
        ]);

        return redirect()->route('teacher.courses.curriculum', $course)->with('success', 'Section created successfully.');
    }

    /**
     * Update the specified course section.
     */
    public function updateSection(UpdateCourseSectionRequest $request, Course $course, CourseSection $section): RedirectResponse
    {
        // $this->authorize('update', $section);

        $section->update($request->validated());

        return redirect()->route('teacher.courses.curriculum', $course)->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified course section.
     */
    public function destroySection(Course $course, CourseSection $section): RedirectResponse
    {
        // $this->authorize('delete', $section);
        
        // Consider what happens to content within the section. For now, let's assume they are soft deleted or handled by DB constraints.
        // Or, explicitly delete/disassociate content.
        // $section->lessons()->update(['course_section_id' => null]); // Example: disassociate
        // $section->quizzes()->update(['course_section_id' => null]);
        // $section->assignments()->update(['course_section_id' => null]);

        $section->delete(); // Assumes soft delete is set up on CourseSection model

        return redirect()->route('teacher.courses.curriculum', $course)->with('success', 'Section deleted successfully.');
    }

    /**
     * Reorder course sections.
     * Expects a request with an ordered array of section IDs.
     * e.g., ['section_ids' => [3, 1, 2]]
     */
    public function reorderSections(Request $request, Course $course): JsonResponse
    {
        // $this->authorize('update', $course);
        $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:course_sections,id', // Ensure IDs are valid sections
        ]);

        foreach ($request->input('section_ids') as $index => $sectionId) {
            CourseSection::where('id', $sectionId)
                         ->where('course_id', $course->id) // Ensure section belongs to the course
                         ->update(['order' => $index + 1]);
        }

        return response()->json(['message' => 'Sections reordered successfully.']);
    }
    
    /**
     * Reorder content (lessons, quizzes, assignments) within a section.
     * Expects a request with an ordered array of content item IDs and their types.
     * e.g., ['items' => [
     *     ['id' => 1, 'type' => 'lesson', 'order' => 1],
     *     ['id' => 1, 'type' => 'quiz', 'order' => 2],
     * ]]
     */
    public function reorderSectionContent(Request $request, Course $course, CourseSection $section): JsonResponse
    {
        // $this->authorize('update', $section);
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.type' => 'required|string|in:lesson,quiz,assignment',
            'items.*.order' => 'required|integer|min:1',
        ]);

        foreach ($request->input('items') as $itemData) {
            $modelClass = match ($itemData['type']) {
                'lesson' => \App\Models\Lesson::class,
                'quiz' => \App\Models\Quiz::class,
                'assignment' => \App\Models\Assignment::class,
                default => null,
            };

            if ($modelClass) {
                $modelClass::where('id', $itemData['id'])
                            ->where('course_section_id', $section->id)
                            ->update(['order' => $itemData['order']]);
            }
        }
        return response()->json(['message' => 'Section content reordered successfully.']);
    }


    /**
     * Show the modal/interface for searching existing course materials to import.
     * This is a placeholder for UI. The actual search query will be via AJAX or another request.
     */
    public function searchCourseMaterials(Request $request, Course $course): JsonResponse // Or View if returning a modal directly
    {
        // $this->authorize('update', $course);
        $term = $request->input('term', '');
        $type = $request->input('type', 'all'); // 'lesson', 'quiz', 'assignment'

        // For now, just return a success message.
        // Actual implementation would search $course->lessons, $course->quizzes, $course->assignments
        // (excluding those already in the target section if adding to a specific one)
        
        $results = [];
        // Example (conceptual - needs refinement for actual models and fields):
        // if ($type === 'lesson' || $type === 'all') {
        //     $results['lessons'] = $course->lessons()->where('title', 'like', "%{$term}%")->get(['id', 'title']);
        // }
        // if ($type === 'quiz' || $type === 'all') {
        //     $results['quizzes'] = $course->quizzes()->where('title', 'like', "%{$term}%")->get(['id', 'title']);
        // }
        // if ($type === 'assignment' || $type === 'all') {
        //     $results['assignments'] = $course->assignments()->where('title', 'like', "%{$term}%")->get(['id', 'title']);
        // }

        return response()->json([
            'message' => 'Search functionality placeholder.',
            'results' => $results // Empty for now
        ]);
    }

    /**
     * Import (copy) a material item to a section.
     * This is a complex operation that involves duplicating models and potentially related data (e.g., quiz questions).
     */
    public function importMaterialToSection(Request $request, Course $course, CourseSection $section): RedirectResponse // Or JsonResponse
    {
        // $this->authorize('update', $section);
        $request->validate([
            'material_id' => 'required|integer',
            'material_type' => 'required|string|in:lesson,quiz,assignment',
        ]);

        $materialId = $request->input('material_id');
        $materialType = $request->input('material_type');
        
        // Placeholder logic:
        // 1. Find the original item (e.g., Lesson::find($materialId)).
        // 2. Ensure it belongs to the current $course (as per plan).
        // 3. Replicate it: $newItem = $originalItem->replicate();
        // 4. Assign to the new $section: $newItem->course_section_id = $section->id;
        // 5. Set new order: $newItem->order = $section->{$materialType . 's'}()->max('order') + 1;
        // 6. If it's a Quiz, replicate its questions and answers (this is non-trivial).
        //    If it's a Lesson with linked quiz/assignment, decide on handling.
        // 7. $newItem->save();

        // For now, just redirect with a message.
        return redirect()->route('teacher.courses.curriculum', $course)
                         ->with('info', "Import functionality for {$materialType} (ID: {$materialId}) to section {$section->id} is a placeholder.");
    }

    /**
     * Display the settings tab for a course.
     */
    public function showSettingsTab(Course $course): View
    {
        // Ensure authorization: user can manage this course
        // $this->authorize('update', $course);

        // Load necessary data for dropdowns
        $categories = \App\Models\Category::orderBy('name')->get();
        $subjects = \App\Models\Subject::orderBy('name')->get();
        $gradeLevels = \App\Models\GradeLevel::orderBy('name')->get();
        
        return view('teacher.courses.edit.settings', compact('course', 'categories', 'subjects', 'gradeLevels'));
    }

    /**
     * Update the course settings.
     */
    public function updateSettings(Request $request, Course $course): RedirectResponse
    {
        // Ensure the teacher owns this course
        if (Auth::id() !== $course->user_id) {
            return redirect()->back()->with('error', 'You are not authorized to update this course.');
        }
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('courses', 'slug')->ignore($course->id)],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'language' => ['nullable', 'string', 'max:50'],
            'tags' => ['nullable', 'string'],
            'what_you_will_learn' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'instructor_info' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_recommended' => ['nullable', 'boolean'],
            'allow_certificate' => ['nullable', 'boolean'],
            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],
            'is_published' => ['required', 'boolean'],
        ]);
        
        // Process tags from comma-separated string to array
        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        } else {
            $validated['tags'] = [];
        }
        
        // Handle thumbnail upload if provided
        if ($request->hasFile('thumbnail')) {
            $request->validate([
                'thumbnail' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);
            
            $file = $request->file('thumbnail');
            
            // Store file in cloud storage
            $cloudPath = Storage::cloud()->put('course-thumbnails', $file);
            
            // Delete old thumbnail if exists
            if ($course->thumbnail_path && Storage::cloud()->exists($course->thumbnail_path)) {
                Storage::cloud()->delete($course->thumbnail_path);
            }
            
            $validated['thumbnail_path'] = $cloudPath;
            $validated['thumbnail_url'] = Storage::cloud()->url($cloudPath);
        }
        
        // Set published_at if published for the first time
        if ($validated['is_published'] && !$course->is_published) {
            $validated['published_at'] = now();
        }
        
        $course->update($validated);
        
        return redirect()->route('teacher.courses.settings', $course)->with('success', 'Course settings updated successfully.');
    }

    /**
     * Display the pricing tab for a course.
     */
    public function showPricingTab(Course $course): View
    {
        // Ensure authorization: user can manage this course
        // $this->authorize('update', $course);
        
        // Load subscription tiers for dropdown
        $subscriptionTiers = \App\Models\SubscriptionTier::orderBy('price')->get();
        
        return view('teacher.courses.edit.pricing', compact('course', 'subscriptionTiers'));
    }

    /**
     * Update the course pricing options.
     */
    public function updatePricing(Request $request, Course $course): RedirectResponse
    {
        // $this->authorize('update', $course);
        
        $validated = $request->validate([
            'pricing_type' => ['required', 'string', 'in:free,paid,subscription'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'also_allow_subscription' => ['nullable', 'boolean'],
            'required_subscription_tier_id' => ['nullable', 'integer', 'exists:subscription_tiers,id'],
            'enable_coupon' => ['nullable', 'boolean'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'sale_start_date' => ['nullable', 'date'],
            'sale_end_date' => ['nullable', 'date', 'after_or_equal:sale_start_date'],
            'enable_bulk_purchase' => ['nullable', 'boolean'],
            'enable_gift_option' => ['nullable', 'boolean'],
        ]);
        
        // Process pricing type
        $updateData = [];
        
        switch ($validated['pricing_type']) {
            case 'free':
                $updateData['price'] = 0;
                $updateData['subscription_required'] = false;
                $updateData['required_subscription_tier_id'] = null;
                break;
            
            case 'paid':
                $updateData['price'] = $validated['price'];
                $updateData['subscription_required'] = isset($validated['also_allow_subscription']);
                
                if ($updateData['subscription_required']) {
                    $updateData['required_subscription_tier_id'] = $validated['required_subscription_tier_id'];
                } else {
                    $updateData['required_subscription_tier_id'] = null;
                }
                break;
            
            case 'subscription':
                $updateData['price'] = 0;
                $updateData['subscription_required'] = true;
                $updateData['required_subscription_tier_id'] = $validated['required_subscription_tier_id'];
                break;
        }
        
        // Add the other pricing fields
        $updateData['enable_coupon'] = isset($validated['enable_coupon']);
        $updateData['sale_price'] = $validated['sale_price'] ?? null;
        $updateData['sale_start_date'] = $validated['sale_start_date'] ?? null;
        $updateData['sale_end_date'] = $validated['sale_end_date'] ?? null;
        $updateData['enable_bulk_purchase'] = isset($validated['enable_bulk_purchase']);
        $updateData['enable_gift_option'] = isset($validated['enable_gift_option']);
        
        $course->update($updateData);
        
        return redirect()->route('teacher.courses.pricing', $course)->with('success', 'Course pricing updated successfully.');
    }

    /**
     * Handle thumbnail upload for a course.
     */
    public function uploadThumbnail(Request $request, Course $course): JsonResponse
    {
        // Ensure the teacher owns this course
        if (Auth::id() !== $course->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'thumbnail' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);
        
        $file = $request->file('thumbnail');
        
        // Store file in cloud storage
        $cloudPath = Storage::cloud()->put('course-thumbnails', $file);
        
        // Delete old thumbnail if exists
        if ($course->thumbnail_path && Storage::cloud()->exists($course->thumbnail_path)) {
            Storage::cloud()->delete($course->thumbnail_path);
        }
        
        // Update course with new thumbnail info
        $course->update([
            'thumbnail_path' => $cloudPath,
            'thumbnail_url' => Storage::cloud()->url($cloudPath)
        ]);
        
        return response()->json([
            'message' => 'Thumbnail uploaded successfully.',
            'thumbnail_url' => $course->thumbnail_url
        ]);
    }

}
