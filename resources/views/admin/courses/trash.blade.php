@extends('layouts.admin')

@section('title', 'Course Trash')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Course Trash</h1>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back to Courses
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.trash') }}" method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search deleted courses..." 
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.courses.trash') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Trashed Courses Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Deleted Courses</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Teacher</th>
                            <th>Category</th>
                            <th>Deleted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($course->thumbnail_url)
                                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="img-thumbnail me-2" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 60px; height: 60px;">
                                                <i class="fa fa-book fa-2x text-secondary"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark">{{ $course->title }}</div>
                                            <p class="small text-muted mb-0">{{ Str::limit($course->short_description, 70) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $course->user->name ?? 'Unknown' }}
                                </td>
                                <td>
                                    <div>{{ $course->category->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $course->subject->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    {{ $course->deleted_at->format('M d, Y H:i') }}
                                    <small class="d-block text-muted">
                                        {{ $course->deleted_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <form action="{{ route('admin.courses.restore', $course->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success me-1" title="Restore Course">
                                                <i class="fa fa-undo"></i> Restore
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('admin.courses.force-delete', $course) }}" method="POST" class="d-inline" 
                                            onsubmit="return confirm('Are you sure you want to permanently delete this course? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete">
                                                <i class="fa fa-trash"></i> Delete Permanently
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-muted mb-0">No deleted courses found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 