<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\LessonController;
use App\Http\Controllers\Teacher\LessonAttachmentController;
use App\Http\Controllers\Teacher\CourseFaqController;
use App\Http\Controllers\Teacher\CourseNoticeController;
use App\Http\Controllers\Teacher\QuizController;
use App\Http\Controllers\Teacher\QuestionController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\SubjectTopicController;
use App\Http\Controllers\Public\CourseController as PublicCourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\CoursePurchaseController;

Route::get('/', function () {
    return view('index');
});

// Public Course Routes (Phase 4.1)
Route::get('/courses', [PublicCourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [PublicCourseController::class, 'show'])->name('courses.show');

Route::get('/events', function () {
    return view('events');
});

Route::get('/blogs', function () {
    return view('blogs-list');
});

Route::get('/instructors', function () {
    return view('instructors-list');
});


Route::get('/instructors/id', function () {
    return view('instructors-single');
});

Route::get('/contact', function () {
    return view('contact');
});


Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.login');
});

// Teacher Course Management Routes
Route::middleware([
    'auth', 
    'verified',
    'role:teacher'
])->prefix('teacher')->name('teacher.')->group(function () {
    
    // Course Resource Routes
    Route::resource('courses', CourseController::class);
    
    // Course Management Tabs
    Route::prefix('courses/{course}')->name('courses.')->group(function () {
        // Curriculum Tab (Subphase 3.3)
        Route::get('curriculum', [CourseController::class, 'showCurriculumTab'])->name('curriculum');
        
        // Settings Tab
        Route::get('settings', [CourseController::class, 'showSettingsTab'])->name('settings');
        Route::put('settings', [CourseController::class, 'updateSettings'])->name('settings.update');
        
        // Pricing Tab
        Route::get('pricing', [CourseController::class, 'showPricingTab'])->name('pricing');
        Route::put('pricing', [CourseController::class, 'updatePricing'])->name('pricing.update');
        
        // Drip Tab
        Route::get('drip', [CourseController::class, 'showDripTab'])->name('drip');
        Route::put('drip', [CourseController::class, 'updateDrip'])->name('drip.update');
        
        // FAQ Tab
        Route::get('faq', [CourseController::class, 'showFaqTab'])->name('faq');
        Route::resource('faq', CourseFaqController::class)->except(['index', 'show'])->shallow();
        Route::post('faq/reorder', [CourseFaqController::class, 'reorder'])->name('faq.reorder');
        
        // Notice Tab
        Route::get('notices', [CourseController::class, 'showNoticeTab'])->name('notices');
        Route::resource('notices', CourseNoticeController::class)->except(['index', 'show'])->shallow();
        
        // Course Status
        Route::put('status', [CourseController::class, 'updateStatus'])->name('status.update');
        
        // Course Thumbnail
        Route::post('thumbnail', [CourseController::class, 'uploadThumbnail'])->name('thumbnail.upload');
    });

    // Course Section Management
    Route::post('courses/{course}/sections', [CourseController::class, 'storeSection'])->name('courses.sections.store');
    Route::put('courses/{course}/sections/{section}', [CourseController::class, 'updateSection'])->name('courses.sections.update');
    Route::delete('courses/{course}/sections/{section}', [CourseController::class, 'destroySection'])->name('courses.sections.destroy');
    Route::post('courses/{course}/sections/reorder', [CourseController::class, 'reorderSections'])->name('courses.sections.reorder');

    // Content within a section (Lessons, Quizzes, Assignments)
    Route::post('courses/{course}/sections/{section}/content/reorder', [CourseController::class, 'reorderSectionContent'])->name('courses.sections.content.reorder');
    
    // Search and Import Materials
    Route::get('courses/{course}/materials/search', [CourseController::class, 'searchCourseMaterials'])->name('courses.materials.search');
    Route::post('courses/{course}/sections/{section}/materials/import', [CourseController::class, 'importMaterialToSection'])->name('courses.sections.materials.import');

    // Lesson Management (Subphase 3.4)
    Route::resource('courses.sections.lessons', LessonController::class)->except([
        'index'
    ])->parameters([
        'sections' => 'section',
        'lessons' => 'lesson'
    ]);

    // Lesson Attachments (Subphase 3.4 - Materials Tab)
    Route::resource('courses.lessons.attachments', LessonAttachmentController::class)
        ->except(['create', 'edit', 'show'])
        ->parameters([
            'lessons' => 'lesson',
            'attachments' => 'attachment'
        ])->shallow();

    Route::post('courses/{course}/lessons/{lesson}/attachments/reorder', [LessonAttachmentController::class, 'reorder'])
        ->name('courses.lessons.attachments.reorder');

    // Quiz Management (Subphase 3.5)
    Route::resource('courses.sections.quizzes', QuizController::class)->except([
        'index'
    ])->parameters([
        'sections' => 'section',
        'quizzes' => 'quiz'
    ]);
    
    Route::post('courses/{course}/sections/{section}/quizzes/reorder', [QuizController::class, 'reorder'])
        ->name('courses.sections.quizzes.reorder');
    
    // Question Management (Subphase 3.5)
    Route::resource('courses.sections.quizzes.questions', QuestionController::class)->except([
        'index'
    ])->parameters([
        'sections' => 'section',
        'quizzes' => 'quiz',
        'questions' => 'question'
    ]);
    
    Route::post('courses/{course}/sections/{section}/quizzes/{quiz}/questions/reorder', [QuestionController::class, 'reorder'])
        ->name('courses.sections.quizzes.questions.reorder');
    
    // Assignment Management (Subphase 3.6)
    Route::resource('courses.sections.assignments', AssignmentController::class)->except([
        'index'
    ])->parameters([
        'sections' => 'section',
        'assignments' => 'assignment'
    ]);
    
    Route::post('courses/{course}/sections/{section}/assignments/reorder', [AssignmentController::class, 'reorder'])
        ->name('courses.sections.assignments.reorder');
    
    // Assignment Submissions (Subphase 3.6)
    Route::get('courses/{course}/sections/{section}/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])
        ->name('courses.assignments.submissions');
    
    Route::get('courses/{course}/sections/{section}/assignments/{assignment}/submissions/{submission}', [AssignmentController::class, 'viewSubmission'])
        ->name('courses.assignments.submissions.show');
    
    Route::put('courses/{course}/sections/{section}/assignments/{assignment}/submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])
        ->name('courses.assignments.submissions.grade');
});

// Admin Course & Taxonomy Management Routes
Route::middleware([
    'auth',
    'verified',
    'role:admin'
])->prefix('admin')->name('admin.')->group(function () {
    // Course Management
    Route::resource('courses', AdminCourseController::class);
    
    // Course Status Management
    Route::put('courses/{course}/status', [AdminCourseController::class, 'updateStatus'])->name('courses.status.update');
    
    // Course Feature Toggles
    Route::put('courses/{course}/featured', [AdminCourseController::class, 'toggleFeatured'])->name('courses.featured.toggle');
    Route::put('courses/{course}/recommended', [AdminCourseController::class, 'toggleRecommended'])->name('courses.recommended.toggle');
    
    // Trash Management
    Route::get('courses/trash', [AdminCourseController::class, 'trash'])->name('courses.trash');
    Route::delete('courses/{course}/force-delete', [AdminCourseController::class, 'forceDelete'])->name('courses.force-delete');
    Route::put('courses/{courseId}/restore', [AdminCourseController::class, 'restore'])->name('courses.restore');
    
    // Course Content Oversight
    Route::get('courses/{course}/curriculum', [AdminCourseController::class, 'showCurriculum'])->name('courses.curriculum');
    
    // Taxonomy Management
    Route::resource('categories', CategoryController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('grade-levels', GradeLevelController::class);
    Route::resource('subject-topics', SubjectTopicController::class);
    
    // Reordering for taxonomies (if needed)
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::post('subjects/reorder', [SubjectController::class, 'reorder'])->name('subjects.reorder');
    Route::post('grade-levels/reorder', [GradeLevelController::class, 'reorder'])->name('grade-levels.reorder');
    Route::post('subject-topics/reorder', [SubjectTopicController::class, 'reorder'])->name('subject-topics.reorder');
});

// Subscription Enrollment Route (Phase 4.2)
Route::middleware('auth')->group(function () {
    Route::post('/courses/{course:slug}/enroll/subscription', [EnrollmentController::class, 'enrollViaSubscription'])->name('courses.enroll.subscription');
    
    // Course Purchase Initiation Route (Phase 4.3)
    Route::post('/courses/{course:slug}/purchase', [CoursePurchaseController::class, 'initiatePurchase'])->name('courses.purchase.initiate');
});

// Payment Gateway Webhook Route (Phase 4.3)
// Ensure CSRF exemption if the gateway doesn't use sessions/cookies. For Laravel, add to VerifyCsrfToken $except array.
Route::post('/payments/webhook/your-gateway', [CoursePurchaseController::class, 'handleGatewayWebhook'])->name('payments.webhook.gateway');

