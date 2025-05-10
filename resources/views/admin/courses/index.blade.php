@extends('layouts.admin')

@section('title', 'Manage Courses')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Courses</h1>
        <div class="d-flex">
            <a href="{{ route('admin.courses.trash') }}" class="btn btn-outline-secondary me-2">
                <i class="fa fa-trash"></i> Trash
            </a>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Create Course
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search courses..." 
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="subject" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="teacher" class="form-select">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('teacher') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Courses Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Courses</h6>
            
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Sort: {{ request('sort', 'created_at') == 'created_at' ? 'Date Created' : ucfirst(str_replace('_', ' ', request('sort', 'created_at'))) }}
                    {{ request('direction', 'desc') == 'desc' ? '↓' : '↑' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => 'asc']) }}">Title (A-Z)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => 'desc']) }}">Title (Z-A)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => 'desc']) }}">Newest First</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => 'asc']) }}">Oldest First</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => 'desc']) }}">Price (High to Low)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => 'asc']) }}">Price (Low to High)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'enrollment_count', 'direction' => 'desc']) }}">Most Enrolled</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Teacher</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Enrollments</th>
                            <th>Created</th>
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
                                            <a href="{{ route('admin.courses.show', $course) }}" class="fw-bold text-dark">{{ $course->title }}</a>
                                            <p class="small text-muted mb-0">{{ Str::limit($course->short_description, 70) }}</p>
                                            
                                            <div class="mt-1">
                                                @if($course->is_featured)
                                                    <span class="badge bg-info me-1">Featured</span>
                                                @endif
                                                @if($course->is_recommended)
                                                    <span class="badge bg-success me-1">Recommended</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.show', $course->user) }}">{{ $course->user->name }}</a>
                                </td>
                                <td>
                                    <div>{{ $course->category->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $course->subject->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($course->is_published)
                                        <span class="badge bg-success">Published</span>
                                        <div class="small mt-1">{{ $course->published_at?->format('M d, Y') }}</div>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    @if($course->price > 0)
                                        ${{ number_format($course->price, 2) }}
                                    @elseif($course->subscription_required)
                                        <span class="badge bg-primary">Subscription</span>
                                    @else
                                        <span class="badge bg-light text-dark">Free</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $course->enrollment_count }}
                                </td>
                                <td>
                                    {{ $course->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon-only" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('admin.courses.show', $course) }}">View Details</a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.courses.edit', $course) }}">Edit Course</a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.courses.curriculum', $course) }}">Manage Curriculum</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            
                                            <!-- Publish/Unpublish -->
                                            <li>
                                                <form action="{{ route('admin.courses.status.update', $course) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_published" value="{{ $course->is_published ? 0 : 1 }}">
                                                    <button type="submit" class="dropdown-item">
                                                        {{ $course->is_published ? 'Unpublish' : 'Publish' }}
                                                    </button>
                                                </form>
                                            </li>
                                            
                                            <!-- Featured Toggle -->
                                            <li>
                                                <form action="{{ route('admin.courses.featured.toggle', $course) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="dropdown-item">
                                                        {{ $course->is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                                                    </button>
                                                </form>
                                            </li>
                                            
                                            <!-- Recommended Toggle -->
                                            <li>
                                                <form action="{{ route('admin.courses.recommended.toggle', $course) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="dropdown-item">
                                                        {{ $course->is_recommended ? 'Remove Recommended' : 'Mark as Recommended' }}
                                                    </button>
                                                </form>
                                            </li>
                                            
                                            <li><hr class="dropdown-divider"></li>
                                            
                                            <!-- Delete -->
                                            <li>
                                                <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Are you sure you want to move this course to trash?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">Move to Trash</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No courses found matching your criteria</p>
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