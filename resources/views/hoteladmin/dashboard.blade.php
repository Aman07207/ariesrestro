@extends('layouts.admin')

@section('title', 'Dashboard — Hotel Admin')
@section('page-title', 'Today at a glance')

@section('content')
<div class="statgrid-admin">
    <div class="stat-tile"><div class="st-label">Active tables</div><div class="st-value">{{ $activeTables }} / {{ $totalTables }}</div></div>
    <div class="stat-tile"><div class="st-label">Active sessions</div><div class="st-value">{{ $activeSessions }}</div></div>
    <div class="stat-tile"><div class="st-label">Revenue in progress</div><div class="st-value">₹{{ number_format($todayRevenue, 0) }}</div></div>
    <div class="stat-tile"><div class="st-label">Menu items</div><div class="st-value">{{ $menuItemCount }}</div></div>
    <div class="stat-tile"><div class="st-label">Staff on record</div><div class="st-value">{{ $staffCount }}</div></div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Quick links</h3></div>
    <div class="panel-body" style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('hoteladmin.tables.index') }}" class="btn btn-outline btn-sm">Manage tables</a>
        <a href="{{ route('hoteladmin.menu-items.index') }}" class="btn btn-outline btn-sm">Manage menu</a>
        <a href="{{ route('hoteladmin.staff.index') }}" class="btn btn-outline btn-sm">Manage staff</a>
    </div>
</div>
@endsection
