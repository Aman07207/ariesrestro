@extends('layouts.admin')

@section('title', $hotel->name.' Tables — Super Admin')
@section('page-title', $hotel->name.' — Tables')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>{{ $hotel->name }}'s tables</h3>
        <a href="{{ route('superadmin.tables.index') }}" class="btn btn-outline btn-sm">&larr; All hotels</a>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Table #</th><th>Seating</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($tables as $table)
                    <tr>
                        <td>Table {{ $table->table_number }}</td>
                        <td>{{ $table->seating_capacity ?? '—' }}</td>
                        <td><span class="badge badge-{{ $table->status->value === 'available' ? 'ready' : ($table->status->value === 'active' ? 'preparing' : 'cancelled') }}">{{ $table->status->value }}</span></td>
                        <td class="row-actions">
                            <a href="{{ route('superadmin.tables.qr', $table) }}" class="btn btn-outline btn-sm">QR code</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:var(--navy-soft);">No tables yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $tables->links() }}
@endsection
