@extends('layouts.app')

@section('title', 'Stock — Aries Restro Chef')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chef.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('chef.queue') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Stock availability</h2><div class="sub">Toggle items off the live menu instantly</div></div>
    </div>
</div>
<div class="content">
    @foreach($menuItems as $item)
        <div class="menuitem" id="stock-item-{{ $item->id }}">
            <div class="menuitem-thumb" style="background:var(--orange-tint);">🍽️</div>
            <div class="menuitem-info"><div class="name">{{ $item->name }}</div><div class="desc">{{ $item->category->name }}</div></div>
            <button type="button" class="btn btn-sm {{ $item->is_available ? 'btn-primary' : 'btn-outline' }}" onclick="toggleStock(this, {{ $item->id }})">
                {{ $item->is_available ? 'Mark unavailable' : 'Mark in stock' }}
            </button>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>window.ARIES_STOCK_URL_BASE = @json(url('/chef/stock'));</script>
<script src="{{ asset('js/chef.js') }}"></script>
@endpush
