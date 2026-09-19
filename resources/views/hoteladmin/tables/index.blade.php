@extends('layouts.admin')

@section('title', 'Tables — Hotel Admin')
@section('page-title', 'Tables')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All tables</h3>
        <a href="{{ route('hoteladmin.tables.create') }}" class="btn btn-primary btn-sm">+ Add table</a>
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
                            <a href="{{ route('hoteladmin.tables.qr', $table) }}" class="btn btn-outline btn-sm">QR code</a>
                            <a href="{{ route('hoteladmin.tables.edit', $table) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('hoteladmin.tables.destroy', $table) }}" onsubmit="return confirm('Remove Table {{ $table->table_number }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Remove</button>
                            </form>
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
