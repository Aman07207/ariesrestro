@extends('layouts.app')

@section('title', 'Kitchen Queue — Aries Restro Chef')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chef.css') }}">
@endpush

@php
    $columns = [
        ['status' => 'pending', 'label' => 'Pending', 'color' => '#B5B2AC', 'action' => 'preparing', 'actionLabel' => 'Start preparing'],
        ['status' => 'preparing', 'label' => 'Preparing', 'color' => 'var(--amber)', 'action' => 'ready', 'actionLabel' => 'Mark ready'],
        ['status' => 'ready', 'label' => 'Ready', 'color' => 'var(--green)', 'action' => 'served', 'actionLabel' => 'Mark served'],
    ];
@endphp

@section('content')
<div class="topbar">
    <div><h2>Kitchen queue</h2><div class="sub">Live orders across all tables</div></div>
    <a href="{{ route('chef.stock') }}" class="iconbtn"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg></a>
</div>
<div class="content" style="padding:14px 0 96px;">
    <div class="kanban">
        @foreach($columns as $col)
            @php $items = $itemsByStatus[$col['status']] ?? collect(); @endphp
            <div class="kancol">
                <h4><i style="background:{{ $col['color'] }}"></i>{{ $col['label'] }} ({{ $items->count() }})</h4>
                @if($items->isEmpty())
                    <div style="font-size:11.5px; color:var(--navy-soft); padding:10px 2px;">Nothing here</div>
                @else
                    @foreach($items as $item)
                        <div class="kancard">
                            <div class="kt">Table {{ $item->order->table->table_number }} · Member {{ $item->order->member_no }}</div>
                            <div class="kn">{{ $item->name }}</div>
                            <div class="kq">Qty {{ $item->quantity }}</div>
                            @if($item->note)<div class="ki">"{{ $item->note }}"</div>@endif
                            <button type="button" class="btn btn-sm btn-dark" style="width:100%;" onclick="advanceItem(this, {{ $item->id }}, '{{ $col['action'] }}')">{{ $col['actionLabel'] }}</button>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
</div>

@include('partials.bottomnav-chef')
@endsection

@push('scripts')
<script src="{{ asset('js/chef.js') }}"></script>
@endpush
