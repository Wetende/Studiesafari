@extends('layouts.app')

@section('title', 'My Courses')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Courses</h5>
                    <a href="{{ route('teacher.courses.create') }}" class="btn btn-primary">Create New Course</a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form action="{{ route('teacher.courses.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search courses..." 
                                        value="{{ request('search') }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>

                            <div class="col-md-3">
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
                                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Course Table -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Course</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Enrollments</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Created</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    @if($course->thumbnail_path)
                                                        <img src="{{ asset('storage/'.$course->thumbnail_path) }}" class="avatar avatar-sm me-3" alt="{{ $course->title }}">
                                                    @else
                                                        <div class="avatar avatar-sm bg-gray-200 me-3 d-flex justify-content-center align-items-center">
                                                            <i class="fa fa-book text-secondary"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $course->title }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ Str::limit($course->short_description, 50) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $course->category->name ?? 'Uncategorized' }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $course->subject->name ?? '' }}</p>
                                        </td>
                                        <td>
                                            @if($course->is_published)
                                                <span class="badge badge-sm bg-success">Published</span>
                                            @else
                                                <span class="badge badge-sm bg-secondary">Draft</span>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                @if($course->price > 0)
                                                    ${{ number_format($course->price, 2) }}
                                                @elseif($course->subscription_required)
                                                    Subscription
                                                @else
                                                    Free
                                                @endif
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $course->enrollment_count }}</p>
                                        </td>
                                        <td>
                                            <span class="text-secondary text-xs font-weight-bold">{{ $course->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon-only" type="button" id="dropdownMenuButton{{ $course->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $course->id }}">
                                                    <li><a class="dropdown-item" href="{{ route('teacher.courses.curriculum', $course) }}">Curriculum</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('teacher.courses.settings', $course) }}">Settings</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('teacher.courses.pricing', $course) }}">Pricing</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('teacher.courses.status.update', $course) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="is_published" value="{{ $course->is_published ? 0 : 1 }}">
                                                            <button type="submit" class="dropdown-item">
                                                                {{ $course->is_published ? 'Unpublish' : 'Publish' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('teacher.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-secondary mb-0">No courses found</p>
                                            <a href="{{ route('teacher.courses.create') }}" class="btn btn-sm btn-primary mt-3">Create Your First Course</a>
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
    </div>
</div>
@endsection 