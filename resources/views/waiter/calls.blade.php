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
    @if($calls->isEmpty())
        <div class="emptystate">
            <div class="em-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/></svg></div>
            <h4>No calls right now</h4>
            <p>When a customer taps "Call Waiter," it'll appear here instantly.</p>
        </div>
    @else
        @foreach($calls as $call)
            <div class="card" style="margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;" id="call-{{ $call->id }}">
                <div>
                    <div style="font-weight:700; font-size:13.5px;">Table {{ $call->table->table_number }}</div>
                    <div class="sub">{{ $call->created_at->diffForHumans() }}</div>
                    @if($call->note)<div class="sub" style="font-style:italic;">"{{ $call->note }}"</div>@endif
                </div>
                @if($call->status->value === 'pending')
                    <form method="POST" action="{{ route('waiter.calls.attend', $call) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-dark">Mark attended</button>
                    </form>
                @else
                    <span class="badge badge-served">Attended</span>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/waiter.js') }}"></script>
@endpush
