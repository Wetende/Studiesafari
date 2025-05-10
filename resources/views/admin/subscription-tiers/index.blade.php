@extends('layouts.admin') {{-- Assuming an admin layout exists --}}

@section('title', 'Subscription Tiers')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subscription Tiers Management</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.subscription-tiers.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add New Tier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Level</th>
                                <th>Duration (Days)</th>
                                <th>Max Courses</th>
                                <th>Active</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tiers as $tier)
                                <tr>
                                    <td>{{ $tier->id }}</td>
                                    <td>{{ $tier->name }}</td>
                                    <td>{{ number_format($tier->price, 2) }}</td>
                                    <td>{{ $tier->level }}</td>
                                    <td>{{ $tier->duration_days == 0 ? 'Unlimited' : $tier->duration_days }}</td>
                                    <td>{{ $tier->max_courses ?? 'Unlimited' }}</td>
                                    <td>
                                        @if ($tier->is_active)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $tier->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if ($tier->trashed())
                                            <span class="badge badge-warning">Soft Deleted</span>
                                        @else
                                            <span class="badge badge-info">Visible</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.subscription-tiers.edit', $tier->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        {{-- Add Show and Delete buttons later --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No subscription tiers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $tiers->links() }} {{-- For pagination --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Add any specific scripts for this page if needed --}}
@endpush 