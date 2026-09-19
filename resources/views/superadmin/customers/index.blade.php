@extends('layouts.admin')

@section('title', 'Active Customers — Super Admin')
@section('page-title', 'Active customer sessions')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>Active table sessions across every hotel</h3>
        <form method="GET" style="display:flex; gap:8px;">
            <select name="hotel" onchange="this.form.submit()">
                <option value="">All hotels</option>
                @foreach($hotels as $h)
                    <option value="{{ $h->id }}" @selected(request('hotel') == $h->id)>{{ $h->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Hotel</th><th>Table</th><th>Members</th><th>Started</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ $session->table->hotel->name }}</td>
                        <td>Table {{ $session->table->table_number }}</td>
                        <td>{{ $session->member_count }}</td>
                        <td>{{ $session->created_at->diffForHumans() }}</td>
                        <td><span class="badge badge-ready">{{ $session->status->value }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:var(--navy-soft);">No active sessions right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $sessions->links() }}
@endsection
