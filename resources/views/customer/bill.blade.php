@extends('layouts.app')

@section('title', 'Table Bill — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('customer.track') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Table bill</h2><div class="sub">Combined total for Table {{ $table->table_number }}</div></div>
    </div>
</div>

<div class="content">
    <div class="billrow"><span>Food subtotal</span><span>₹{{ number_format($bill->food_base_amount, 2) }}</span></div>
    @if($bill->alcohol_base_amount > 0)
        <div class="billrow"><span>Alcohol subtotal</span><span>₹{{ number_format($bill->alcohol_base_amount, 2) }}</span></div>
    @endif

    <hr class="dash">

    <form method="POST" action="{{ route('customer.bill.service-charge') }}">
        @csrf
        <label style="display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; margin-bottom:10px;">
            <input type="checkbox" name="service_charge_consent" value="1" style="width:auto;"
                onchange="this.form.submit()" @checked($bill->service_charge_consent)>
            Add service charge ({{ rtrim(rtrim(number_format($table->hotel->default_service_charge_percent ?? 0, 2), '0'), '.') }}% — optional, you choose)
        </label>
    </form>

    <div class="billrow"><span>Service charge</span><span>₹{{ number_format($bill->service_charge_amount, 2) }}</span></div>

    <hr class="dash">

    <div class="billrow"><span>CGST</span><span>₹{{ number_format($bill->cgst_amount, 2) }}</span></div>
    <div class="billrow"><span>SGST</span><span>₹{{ number_format($bill->sgst_amount, 2) }}</span></div>
    @if($bill->alcohol_base_amount > 0)
        <div class="billrow"><span>VAT</span><span>₹{{ number_format($bill->vat_amount, 2) }}</span></div>
    @endif

    <div class="billrow total"><span>Grand Total</span><span>₹{{ number_format($bill->grand_total, 2) }}</span></div>
    <a href="{{ route('customer.pay') }}" class="btn btn-primary" style="margin-top:20px;">Pay with Razorpay</a>
</div>
@endsection
