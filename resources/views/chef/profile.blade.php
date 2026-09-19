@extends('layouts.app')

@section('title', 'My Profile — Aries Restro Chef')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chef.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('chef.queue') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>My profile</h2><div class="sub">Staff account &amp; shift details</div></div>
    </div>
</div>

<div class="content">
    <div class="profile-hero">
        <div class="avatar">{{ collect(explode(' ', $chef->name))->map(fn($p) => strtoupper($p[0]))->join('') }}</div>
        <div>
            <div class="pname">{{ $chef->name }}</div>
            <div class="prole">Head Chef · Employee ID {{ $chef->employee_id }}</div>
        </div>
    </div>
    <div class="sectiontitle">Shift details</div>
    <div class="card infolist">
        <div class="inforow"><span>Station</span><b>{{ $chef->section ?? '—' }}</b></div>
        <div class="inforow"><span>Shift</span><b>{{ $chef->shift ?? '—' }}</b></div>
        <div class="inforow"><span>Status</span><span class="badge badge-ready">On duty</span></div>
    </div>
    <div class="sectiontitle">Today's summary</div>
    <div class="statgrid">
        <div class="statcard"><div class="sv">42</div><div class="sl">Items prepared</div></div>
        <div class="statcard"><div class="sv">7</div><div class="sl">In queue</div></div>
        <div class="statcard"><div class="sv">12m</div><div class="sl">Avg prep time</div></div>
    </div>
    <div class="sectiontitle">Account</div>
    <div class="card infolist">
        <div class="inforow"><span>Phone number</span><b>{{ $chef->phone ?? '—' }}</b></div>
        <div class="inforow"><span>Hotel</span><b>{{ $chef->hotel->name }}, {{ $chef->hotel->address }}</b></div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline" style="margin-top:20px; color:var(--red); border-color:var(--red-tint);">Log out</button>
    </form>
</div>

@include('partials.bottomnav-chef')
@endsection
