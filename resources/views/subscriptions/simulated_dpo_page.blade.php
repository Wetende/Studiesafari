@extends('layouts.app')

@section('title', 'Simulated Payment Page')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Simulated DPO Payment Gateway</h4>
                </div>
                <div class="card-body text-center">
                    <p class="lead">This is a simulated payment page for testing purposes.</p>
                    
                    <h5>Payment Details:</h5>
                    <ul class="list-unstyled mb-4">
                        <li><strong>Payment ID:</strong> {{ $payment->id }}</li>
                        <li><strong>Amount:</strong> {{ $payment->currency }} {{ number_format($payment->amount, 2) }}</li>
                        <li><strong>Gateway Reference:</strong> {{ $payment->gateway_reference_id }}</li>
                        <li><strong>Status:</strong> <span class="badge badge-warning text-uppercase">{{ $payment->status }}</span></li>
                    </ul>

                    <p>Please choose an outcome for this simulated payment:</p>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <form action="{{ route('subscriptions.paymentCallback') }}" method="GET" class="d-inline">
                                <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                <input type="hidden" name="status" value="success">
                                <input type="hidden" name="transaction_id" value="DPO_SIM_SUCCESS_{{ strtoupper(Str::random(10)) }}">
                                <button type="submit" class="btn btn-success btn-lg btn-block">Simulate Payment Success</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('subscriptions.paymentCallback') }}" method="GET" class="d-inline">
                                <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                <input type="hidden" name="status" value="failure">
                                <input type="hidden" name="transaction_id" value="DPO_SIM_FAILURE_{{ strtoupper(Str::random(10)) }}">
                                <button type="submit" class="btn btn-danger btn-lg btn-block">Simulate Payment Failure</button>
                            </form>
                        </div>
                    </div>

                    <p class="mt-4 text-muted small">
                        In a real scenario, you would be redirected to the DPO Global Payment page. 
                        After payment, DPO would redirect back to our site (callback URL) with the transaction status.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 