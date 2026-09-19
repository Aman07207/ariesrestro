@extends('layouts.app')

@section('title', 'Payment Successful — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
<div style="flex:1; display:flex; align-items:center; justify-content:center;">
    <div class="emptystate">
        <div class="em-ic" style="background:var(--green-tint); color:var(--green);"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></div>
        <h4>Payment successful</h4>
        <p>₹{{ number_format($bill->grand_total, 2) }} paid via Razorpay. Table {{ $table->table_number }} has been closed — thanks for dining with us!</p>
        <a href="{{ route('customer.scan-help') }}" class="btn btn-outline" style="margin-top:22px; width:220px;">Start a new session</a>
    </div>
</div>
@endsection
