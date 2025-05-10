@extends('layouts.admin')

@section('title', 'Create Subscription Tier')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Add New Subscription Tier</h3>
                </div>
                <form action="{{ route('admin.subscription-tiers.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="price">Price <span class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" step="0.01" min="0" required>
                                    @error('price')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="level">Level (Hierarchy) <span class="text-danger">*</span></label>
                                    <input type="number" name="level" id="level" class="form-control @error('level') is-invalid @enderror" value="{{ old('level') }}" step="1" min="0" required>
                                    @error('level')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="duration_days">Duration (Days) <span class="text-danger">*</span></label>
                                    <input type="number" name="duration_days" id="duration_days" class="form-control @error('duration_days') is-invalid @enderror" value="{{ old('duration_days') }}" step="1" min="0" required>
                                    <small class="form-text text-muted">Enter 0 for unlimited duration.</small>
                                    @error('duration_days')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="max_courses">Max Courses</label>
                                    <input type="number" name="max_courses" id="max_courses" class="form-control @error('max_courses') is-invalid @enderror" value="{{ old('max_courses') }}" step="1" min="0">
                                    <small class="form-text text-muted">Leave blank or 0 for unlimited courses (if applicable by design).</small>
                                    @error('max_courses')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="features">Features (JSON format)</label>
                            <textarea name="features" id="features" class="form-control @error('features') is-invalid @enderror" rows="4">{{ old('features', '[\n  \"Feature 1\",\n  \"Feature 2\"\n]') }}</textarea>
                            <small class="form-text text-muted">Enter features as a JSON array of strings. E.g., ["Feature A", "Feature B"]</small>
                            @error('features')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Is Active</label>
                            </div>
                            @error('is_active')
                                <span class="text-danger d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Tier</button>
                        <a href="{{ route('admin.subscription-tiers.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 