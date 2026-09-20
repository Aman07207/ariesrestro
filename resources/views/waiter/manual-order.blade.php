@extends('layouts.app')

@section('title', 'Manual Order — Aries Restro Waiter')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/waiter.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('waiter.tables') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Manual order entry</h2><div class="sub">For walk-ins, or adding to a seated table</div></div>
    </div>
</div>

<div class="content">
    <label class="flabel">Table</label>
    <select id="manual-table">
        @foreach($tables as $t)
            <option value="{{ $t->id }}">Table {{ $t->table_number }} ({{ ucfirst(str_replace('_', ' ', $t->status->value)) }})</option>
        @endforeach
    </select>

    <div class="sectiontitle">Add items</div>
    <label class="flabel">Menu item</label>
    <select id="manual-item">
        @foreach($menuItems as $item)
            <option value="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $item->price }}">{{ $item->name }} — ₹{{ number_format($item->price, 0) }}</option>
        @endforeach
    </select>
    <label class="flabel">Quantity</label>
    <input type="number" id="manual-qty" value="1" min="1" max="20">
    <label class="flabel">Special instructions</label>
    <textarea id="manual-note" placeholder="e.g. less spicy, no onions"></textarea>
    <button type="button" class="btn btn-outline" style="margin-top:12px;" onclick="addManualLine()">+ Add to list</button>

    <div class="sectiontitle">Order so far</div>
    <div id="manual-lines"><p class="smallmute" style="text-align:left;">Nothing added yet.</p></div>

    <button type="button" class="btn btn-primary" id="manual-send" style="margin-top:20px;" onclick="submitManualOrder()">Send to kitchen</button>
</div>

@include('partials.bottomnav-waiter')
@endsection

@push('scripts')
<script>window.ARIES_MANUAL_ORDER_URL = @json(route('waiter.manual-order.store'));</script>
<script src="{{ asset('js/waiter.js') }}"></script>
@endpush
