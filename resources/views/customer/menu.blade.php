@extends('layouts.app')

@section('title', 'Menu — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
<div class="menu-header">
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('customer.scan-help') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Table {{ $table->table_number }}</h2><div class="sub">You're Member {{ $memberNo }} · Session active</div></div>
    </div>
    <button type="button" class="iconbtn" onclick="openWaiterCallModal()"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg></button>
</div>

<div class="cattabs" id="cattabs">
    <div class="cattab active" data-cat="all">All</div>
    @foreach($categories as $category)
        <div class="cattab" data-cat="cat-{{ $category->id }}">{{ $category->name }}</div>
    @endforeach
</div>

<div class="filterrow">
    <div class="filterchip active" id="filter-all" data-veg="all">All</div>
    <div class="filterchip" id="filter-veg" data-veg="veg"><span class="badge-veg"></span> Veg</div>
    <div class="filterchip" id="filter-nonveg" data-veg="nonveg"><span class="badge-veg badge-nonveg"></span> Non-veg</div>
</div>
</div>

{{-- Bottom padding clears BOTH fixed layers: the bottom nav (~70px) and the floating cart bar above it (~60px). --}}
<div class="content" id="menu-list" style="padding-bottom:170px;">
    {{-- Scrolls away with the list below the sticky menu-header, matching the demo's
         greeting card scrolling under a lone sticky topbar rather than eating
         permanent space in this app's pinned topbar+tabs+filter header. --}}
    <div class="greeting-card theme-{{ $season->value }}">
        <span class="greeting-deco">{{ $season->deco() }}</span>
        <span class="greeting-deco">{{ $season->deco() }}</span>
        <span class="greeting-deco">{{ $season->deco() }}</span>
        <div class="g-icon">{{ $greeting['icon'] }}</div>
        <div class="g-title">Howdy! Welcome to {{ $greeting['hotelName'] }}</div>
        <div class="g-sub">{{ $greeting['text'] }} — perfect for {{ $season->label() }} 🍽️</div>
    </div>

    @foreach($categories as $category)
        @foreach($category->menuItems as $item)
            <div class="menuitem" data-cat="cat-{{ $category->id }}" data-veg="{{ $item->veg_type === 'veg' ? 'veg' : 'nonveg' }}" data-id="{{ $item->id }}" data-price="{{ $item->price }}" data-name="{{ $item->name }}">
                <div class="menuitem-thumb" style="background:var(--orange-tint);">🍽️</div>
                <div class="menuitem-info">
                    <div class="name">
                        <span class="badge-veg {{ $item->veg_type === 'veg' ? '' : 'badge-nonveg' }}"></span>
                        {{ $item->name }}
                        @if($item->is_popular)<span class="tag-popular">Popular</span>@endif
                    </div>
                    <div class="pricerow">
                        <span class="price">₹{{ number_format($item->price, 0) }}</span>
                        @if($item->avg_rating)<span class="rating">★ {{ $item->avg_rating }}</span>@endif
                    </div>
                    <div class="qty-slot" data-qty-slot="{{ $item->id }}">
                        <button type="button" class="addbtn" onclick="cartChangeQty({{ $item->id }}, 1)">Add +</button>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
</div>

<a href="{{ route('customer.cart') }}" class="stickycart hidden" id="stickycart">
    <div><div class="l" id="sticky-count">0 items</div><div class="amt" id="sticky-amt">₹0</div></div>
    <div class="r">View cart <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></div>
</a>

@include('partials.bottomnav-customer')
@endsection

@push('modals')
<div class="overlay center-modal" id="overlay-waitercall"></div>
@endpush

@push('scripts')
<script src="{{ asset('js/customer.js') }}"></script>
@endpush
