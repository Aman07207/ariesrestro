@extends('layouts.app')

@section('title', 'Aries Restro — Scan to Order')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
<div class="scanwrap">
    <div class="scanbox">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM19 14v3M14 19h3M19 19h.01"/></svg>
    </div>
    <h2>{{ $heading ?? 'Scan the table QR code' }}</h2>
    <p>{{ $message ?? "No app to download, no login needed. Point your camera at the QR code on your table to see the menu." }}</p>
</div>
@endsection
