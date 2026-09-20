@extends('layouts.app')

@section('title', 'Order Status — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('customer.menu') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Order status</h2><div class="sub">Table {{ $table->table_number }} · All members</div></div>
    </div>
    <button type="button" class="iconbtn" onclick="openWaiterCallModal()"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/></svg></button>
</div>

<div class="content">
    <livewire:customer.track-order :session-id="$sessionId" :member-no="$memberNo" />
</div>

@include('partials.bottomnav-customer')
@endsection

@push('modals')
<div class="overlay center-modal" id="overlay-waitercall"></div>
@endpush

@push('scripts')
<script src="{{ asset('js/customer.js') }}"></script>
@endpush
