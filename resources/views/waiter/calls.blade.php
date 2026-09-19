@extends('layouts.app')

@section('title', 'Waiter Calls — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/waiter.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('waiter.tables') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Waiter calls</h2><div class="sub">Live alerts from customer tables</div></div>
    </div>
</div>

<div class="content">
    <livewire:waiter.calls-index />
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/waiter.js') }}"></script>
@endpush
