@extends('layouts.app')

@section('title', 'Confirm Subscription: ' . $subscriptionTier->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Confirm Your Subscription</h4>
                </div>
                <div class="card-body">
                    <h2 class="card-title">{{ $subscriptionTier->name }}</h2>
                    <p class="lead">{{ $subscriptionTier->description }}</p>
                    
                    <hr>

                    <h4>Plan Details:</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Price:
                            <span class="font-weight-bold">${{ number_format($subscriptionTier->price, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Duration:
                            <span class="font-weight-bold">{{ $subscriptionTier->duration_days > 0 ? $subscriptionTier->duration_days . ' days' : 'Lifetime' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Max Courses:
                            <span class="font-weight-bold">{{ $subscriptionTier->max_courses ?? 'Unlimited' }}</span>
                        </li>
                    </ul>

                    @if($subscriptionTier->features && is_array($subscriptionTier->features) && count($subscriptionTier->features) > 0)
                        <h5>Features Included:</h5>
                        <ul class="list-unstyled mb-4">
                            @foreach ($subscriptionTier->features as $feature)
                                <li class="mb-1"><i class="fas fa-check text-success mr-2"></i>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <form action="{{ route('subscriptions.process', $subscriptionTier->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            Proceed to Payment Simulation
                        </button>
                    </form>
                    <a href="{{ route('pricing.index') }}" class="btn btn-link btn-block mt-3">Choose a different plan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 