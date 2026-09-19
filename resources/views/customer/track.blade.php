@extends('layouts.app')

@section('title', 'Order Status — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@php
    $steps = ['pending' => 'Placed', 'preparing' => 'Preparing', 'ready' => 'Ready', 'served' => 'Served'];
    $stepKeys = array_keys($steps);
    $activeItems = $orderItems->filter(fn($i) => $i->status !== \App\Enums\OrderItemStatus::Cancelled);
    $overallIdx = $activeItems->isEmpty()
        ? 0
        : $activeItems->min(fn($i) => array_search($i->status->value, $stepKeys));
@endphp

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('customer.menu') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Order status</h2><div class="sub">Table {{ $table->table_number }} · All members</div></div>
    </div>
    <button type="button" class="iconbtn" onclick="openWaiterCallModal()"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/></svg></button>
</div>

<div class="content">
    @if($orderItems->isEmpty())
        <div class="emptystate">
            <div class="em-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
            <h4>No orders yet</h4>
            <p>Once you place an order it'll show up here with live status.</p>
        </div>
    @else
        <div class="steprow">
            @foreach($stepKeys as $i => $key)
                @php $done = $i <= $overallIdx; @endphp
                <div class="stepcol"><div class="stepdot {{ $done ? 'done' : '' }}">{{ $i + 1 }}</div><div class="steplabel">{{ $steps[$key] }}</div></div>
                @if(!$loop->last)
                    <div class="stepline {{ $i < $overallIdx ? 'done' : '' }}"></div>
                @endif
            @endforeach
        </div>
        <hr class="dash">

        @foreach($orderItems->groupBy('order.member_no') as $memberNumber => $items)
            <div class="memberblock">
                <div class="memberhead">👤 Member {{ $memberNumber }}{{ $memberNumber == $memberNo ? ' (You)' : '' }}</div>
                @foreach($items as $item)
                    <div class="orderitem">
                        <div>
                            <div class="oi-name">{{ $item->name }}</div>
                            <div class="oi-qty">Qty {{ $item->quantity }}{{ $item->note ? ' · '.$item->note : '' }}</div>
                        </div>
                        <span class="badge badge-{{ $item->status->value }}">{{ $item->status->value }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="smallmute">Status updates here live once a table order changes in the kitchen.</div>
    @endif
</div>

@include('partials.bottomnav-customer')
@endsection

@push('modals')
<div class="overlay center-modal" id="overlay-waitercall"></div>
@endpush

@push('scripts')
<script src="{{ asset('js/customer.js') }}"></script>
@endpush
