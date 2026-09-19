@extends('layouts.app')

@section('title', 'Payment — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@php
    $methods = ['UPI', 'Credit / Debit card', 'Netbanking', 'Wallet'];
@endphp

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('customer.bill') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Payment</h2><div class="sub">Powered by Razorpay</div></div>
    </div>
</div>

<div class="content">
    <div class="card" style="text-align:center; margin-bottom:18px;">
        <div class="sub" style="margin-bottom:4px;">Amount payable</div>
        <div style="font-size:28px; font-weight:800;">₹{{ number_format($bill->grand_total, 2) }}</div>
    </div>
    <div class="sectiontitle">Choose payment method</div>
    <div id="pay-methods">
        @foreach($methods as $i => $method)
            <div class="filterchip {{ $i === 0 ? 'active' : '' }}" style="width:100%; justify-content:flex-start; margin-bottom:8px; padding:12px 14px;" onclick="selectPayMethod(this)">{{ $method }}</div>
        @endforeach
    </div>
    <label class="flabel">Phone number (for UPI/OTP)</label>
    <input type="text" value="98765 43210">
    <form method="POST" action="{{ route('customer.pay.confirm') }}">
        @csrf
        <button type="submit" class="btn btn-primary" style="margin-top:22px;">Pay now</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/customer.js') }}"></script>
@endpush
