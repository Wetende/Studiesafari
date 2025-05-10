@extends('layouts.admin')

@section('title', 'View Subscription Tier - ' . $subscriptionTier->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subscription Tier Details: <strong>{{ $subscriptionTier->name }}</strong></h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.subscription-tiers.edit', $subscriptionTier->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.subscription-tiers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 25%;">ID</th>
                                <td>{{ $subscriptionTier->id }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td>{{ $subscriptionTier->name }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $subscriptionTier->description ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Price</th>
                                <td>{{ number_format($subscriptionTier->price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Level (Hierarchy)</th>
                                <td>{{ $subscriptionTier->level }}</td>
                            </tr>
                            <tr>
                                <th>Duration (Days)</th>
                                <td>{{ $subscriptionTier->duration_days == 0 ? 'Unlimited' : $subscriptionTier->duration_days . ' days' }}</td>
                            </tr>
                            <tr>
                                <th>Max Courses</th>
                                <td>{{ $subscriptionTier->max_courses ?? 'Unlimited' }}</td>
                            </tr>
                            <tr>
                                <th>Features</th>
                                <td>
                                    @if($subscriptionTier->features)
                                        <ul>
                                            @foreach($subscriptionTier->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Is Active</th>
                                <td>
                                    @if ($subscriptionTier->is_active)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                             <tr>
                                <th>Status</th>
                                <td>
                                    @if ($subscriptionTier->trashed())
                                        <span class="badge badge-warning">Soft Deleted</span>
                                    @else
                                        <span class="badge badge-info">Visible</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $subscriptionTier->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $subscriptionTier->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                             @if ($subscriptionTier->trashed())
                            <tr>
                                <th>Deleted At</th>
                                <td>{{ $subscriptionTier->deleted_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 