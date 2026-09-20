@extends('layouts.app')

@section('title', 'Kitchen Queue — Aries Restro Chef')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chef.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div><h2>Kitchen queue</h2><div class="sub">Live orders across all tables</div></div>
    <a href="{{ route('chef.stock') }}" class="iconbtn"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg></a>
</div>
<livewire:chef.queue-board />

@include('partials.bottomnav-chef')
@endsection

@push('scripts')
<script src="{{ asset('js/chef.js') }}"></script>
@endpush
