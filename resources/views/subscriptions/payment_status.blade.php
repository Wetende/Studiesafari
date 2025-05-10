@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    @if ($status === 'success')
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h2 class="card-title text-success">Payment Successful!</h2>
                        <p class="lead">Your payment has been processed successfully.</p>
                        @if ($payment)
                            <p>Your Subscription for <strong>{{ $payment->payable->name ?? 'the selected tier' }}</strong> should be active shortly.</p>
                            <p class="text-muted small">Payment ID: {{ $payment->id }} | Transaction ID: {{ $payment->gateway_reference_id }}</p>
                        @endif
                        <a href="{{ route('dashboard') }}" class="btn btn-success mt-3">Go to Dashboard</a>
                    @elseif ($status === 'failure')
                        <div class="mb-4">
                            <i class="fas fa-times-circle fa-5x text-danger"></i>
                        </div>
                        <h2 class="card-title text-danger">Payment Failed</h2>
                        <p class="lead">Unfortunately, your payment could not be processed at this time.</p>
                        @if ($payment)
                            <p class="text-muted small">Payment ID: {{ $payment->id }} | Transaction ID: {{ $payment->gateway_reference_id }}</p>
                        @endif
                        <p>Please try again or contact support if the issue persists.</p>
                        <a href="{{ route('pricing.index') }}" class="btn btn-warning mt-3">Try Another Plan</a>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-3">Go to Dashboard</a>
                    @else
                        <div class="mb-4">
                            <i class="fas fa-question-circle fa-5x text-muted"></i>
                        </div>
                        <h2 class="card-title">Unknown Payment Status</h2>
                        <p class="lead">We could not determine the status of your payment.</p>
                        <p>Please check your dashboard or contact support.</p>
                        <a href="{{ route('dashboard') }}" class="btn btn-info mt-3">Go to Dashboard</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 