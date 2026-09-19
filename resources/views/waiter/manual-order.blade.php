@extends('layouts.app')

@section('title', 'Manual Order — Aries Restro Waiter')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/waiter.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('waiter.tables') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Manual order entry</h2><div class="sub">For walk-ins without a QR scan</div></div>
    </div>
</div>

<div class="content">
    <label class="flabel">Table number</label>
    <select id="manual-table">
        @foreach($availableTables as $t)
            <option value="{{ $t->id }}">Table {{ $t->table_number }} (Available)</option>
        @endforeach
    </select>
    <label class="flabel">Menu item</label>
    <select id="manual-item">
        @foreach($menuItems as $item)
            <option value="{{ $item->id }}">{{ $item->name }} — ₹{{ number_format($item->price, 0) }}</option>
        @endforeach
    </select>
    <label class="flabel">Quantity</label>
    <input type="number" id="manual-qty" value="1" min="1">
    <label class="flabel">Special instructions</label>
    <textarea id="manual-note" placeholder="e.g. less spicy, no onions"></textarea>
    <button type="button" class="btn btn-primary" style="margin-top:20px;" onclick="submitManualOrder()">Add to order</button>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/waiter.js') }}"></script>
@endpush
