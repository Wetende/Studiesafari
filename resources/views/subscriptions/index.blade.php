@extends('layouts.app') {{-- Assuming a general app layout --}}

@section('title', 'Our Pricing Plans')

@section('content')
<div class="container py-5">
    <div class="row text-center mb-5">
        <div class="col-lg-8 mx-auto">
            <h1 class="display-4">Our Pricing Plans</h1>
            <p class="lead mb-0">Choose the plan that best fits your learning needs.</p>
        </div>
    </div>

    <div class="row">
        @forelse ($tiers as $tier)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0 {{ $tier->level == 0 ? 'border-primary' : ( $tier->level == 1 ? 'border-secondary' : ( $tier->level == 2 ? 'border-info' : 'border-warning') ) }}">
                    <div class="card-header bg-transparent py-4 border-0">
                        <h4 class="mb-0 text-center">{{ $tier->name }}</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <h2 class="font-weight-bold">${{ number_format($tier->price, 2) }}
                                <span class="text-muted small">/ {{ $tier->duration_days > 0 ? $tier->duration_days . ' days' : 'Lifetime' }}</span>
                            </h2>
                        </div>
                        
                        <p class="text-muted">{{ $tier->description }}</p>
                        
                        @if($tier->features && is_array($tier->features) && count($tier->features) > 0)
                            <ul class="list-unstyled mb-4">
                                @foreach ($tier->features as $feature)
                                    <li class="mb-2"><i class="fas fa-check text-primary mr-2"></i>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-center font-italic">No specific features listed.</p>
                        @endif

                        @if($tier->max_courses)
                            <p class="text-muted small">Max Courses: {{ $tier->max_courses }}</p>
                        @else
                             <p class="text-muted small">Max Courses: Unlimited</p>
                        @endif
                        
                        <a href="{{ route('subscriptions.showSubscribeForm', $tier->id) }}" 
                           class="btn btn-block btn-primary text-uppercase font-weight-bold py-2 {{ $tier->price == 0 ? 'btn-outline-success' : 'btn-primary' }}">
                           {{ $tier->price == 0 ? 'Get Started' : 'Choose Plan' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    No subscription plans are currently available. Please check back later.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>
@endpush 