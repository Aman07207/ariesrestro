@extends('layouts.admin')

@section('title', 'Dashboard — Super Admin')
@section('page-title', 'Platform overview')

@section('content')
<div class="statgrid-admin">
    <div class="stat-tile"><div class="st-label">Total hotels</div><div class="st-value">{{ $totalHotels }}</div></div>
    <div class="stat-tile"><div class="st-label">Active hotels</div><div class="st-value">{{ $activeHotels }}</div></div>
    <div class="stat-tile"><div class="st-label">Active subscriptions</div><div class="st-value">{{ $activeSubscriptions }}</div></div>
    <div class="stat-tile"><div class="st-label">Total staff</div><div class="st-value">{{ $totalStaff }}</div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3>Recent activity</h3>
        <a href="{{ route('superadmin.activity.index') }}" class="btn btn-outline btn-sm">View all</a>
    </div>
    <div class="panel-body table-scroll">
        @if($recentActivity->isEmpty())
            <p style="color:var(--navy-soft); font-size:13px;">No activity recorded yet.</p>
        @else
            <table class="admin-table">
                <thead><tr><th>When</th><th>Log</th><th>Description</th><th>Causer</th></tr></thead>
                <tbody>
                    @foreach($recentActivity as $activity)
                        <tr>
                            <td>{{ $activity->created_at->diffForHumans() }}</td>
                            <td>{{ $activity->log_name }}</td>
                            <td>{{ $activity->description }}</td>
                            <td>{{ $activity->causer?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
