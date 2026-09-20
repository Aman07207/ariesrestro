@extends('layouts.app')

@section('title', 'Tables — Aries Restro Waiter')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/waiter.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div><h2>Tables</h2><div class="sub">{{ $hotel->name }} · Ground floor</div></div>
    <a href="{{ route('waiter.calls') }}" class="iconbtn" style="position:relative">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
        <livewire:waiter.calls-badge :extra-style="'top:-4px; right:-4px;'" />
    </a>
</div>
<div class="legend">
    <span><i style="background:var(--green)"></i>Available</span>
    <span><i style="background:var(--amber)"></i>Active</span>
    <span><i style="background:var(--red)"></i>Bill requested</span>
</div>
<div class="content">
    <livewire:waiter.tables-grid />
</div>

@include('partials.bottomnav-waiter')
@endsection
