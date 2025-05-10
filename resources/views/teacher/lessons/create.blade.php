@extends('layouts.teacher.app') {{-- Or your main teacher layout --}}

@section('title', 'Create New Lesson for ' . $section->title)

@push('styles')
    {{-- Styles for rich text editor if needed --}}
    {{-- <link rel="stylesheet" href="{{ asset('path/to/tinymce.css') }}"> --}}
@endpush

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 fw-700 text-dark-1">Create New Lesson <span class="badge bg-blue-1 text-white fw-400">{{ Str::title(str_replace('_', ' ', $lessonType)) }}</span></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('teacher.courses.index') }}">My Courses</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('teacher.courses.curriculum', $course->id) }}">{{ $course->title }} - Curriculum</a></li>
                            <li class="breadcrumb-item active">Create Lesson</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card -dark-bg-light-1">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-dark-1">Lesson Details for Section: <span class="fw-500">{{ $section->title }}</span></h5>
                        <a href="{{ route('teacher.courses.curriculum', $course->id) }}" class="button -sm -outline-dark-1 text-dark-1">Back to Curriculum</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('teacher.courses.sections.lessons.store', [$course, $section]) }}" method="POST" enctype="multipart/form-data"> {{-- Added enctype for potential file uploads --}}
                            @csrf
                            
                            @include('teacher.lessons.partials._form_common', compact('course', 'section', 'lesson', 'lessonType', 'availableQuizzes', 'availableAssignments'))
                            
                            <div class="mt-4">
                                <button type="submit" class="button -md -blue-1 text-white">Create Lesson</button>
                                <a href="{{ route('teacher.courses.curriculum', $course->id) }}" class="button -md -outline-dark-1 text-dark-1">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Scripts for rich text editor if needed --}}
    {{-- <script src="{{ asset('path/to/tinymce.js') }}"></script> --}}
    {{-- <script>
        // Basic TinyMCE init (example)
        // tinymce.init({
        //     selector: '.rich-text-editor',
        //     plugins: 'code table lists image media link',
        //     toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | image media link'
        // });
    </script> --}}
@endpush 