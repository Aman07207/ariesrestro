@extends('layouts.app')

@section('title', 'My Profile — Aries Restro Waiter')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/waiter.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('waiter.tables') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>My profile</h2><div class="sub">Staff account &amp; shift details</div></div>
    </div>
</div>

<div class="content">
    <div class="profile-hero">
        <div class="avatar">{{ collect(explode(' ', $waiter->name))->map(fn($p) => strtoupper($p[0]))->join('') }}</div>
        <div>
            <div class="pname">{{ $waiter->name }}</div>
            <div class="prole">Waiter · Employee ID {{ $waiter->employee_id }}</div>
        </div>
    </div>
    <div class="sectiontitle">Shift details</div>
    <div class="card infolist">
        <div class="inforow"><span>Section</span><b>{{ $waiter->section ?? '—' }}</b></div>
        <div class="inforow"><span>Shift</span><b>{{ $waiter->shift ?? '—' }}</b></div>
        <div class="inforow"><span>Status</span><span class="badge badge-ready">On duty</span></div>
    </div>
    <div class="sectiontitle">Today's summary</div>
    <div class="statgrid">
        <div class="statcard"><div class="sv">18</div><div class="sl">Tables served</div></div>
        <div class="statcard"><div class="sv">3</div><div class="sl">Calls attended</div></div>
        <div class="statcard"><div class="sv">₹9,240</div><div class="sl">Bills closed</div></div>
    </div>
    <div class="sectiontitle">Account</div>
    <div class="card infolist">
        <div class="inforow"><span>Phone number</span><b>{{ $waiter->phone ?? '—' }}</b></div>
        <div class="inforow"><span>Hotel</span><b>{{ $waiter->hotel->name }}, {{ $waiter->hotel->address }}</b></div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline" style="margin-top:20px; color:var(--red); border-color:var(--red-tint);">Log out</button>
    </form>
</div>

@include('partials.bottomnav-waiter')
@endsection
