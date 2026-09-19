@extends('layouts.admin')

@section('title', 'Tables & QR Codes — Super Admin')
@section('page-title', 'Tables & QR codes')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>Pick a hotel</h3>
        <form method="GET" style="display:flex; gap:8px;">
            <input type="text" name="search" placeholder="Search hotels…" value="{{ request('search') }}" style="width:220px;">
            <button type="submit" class="btn btn-outline btn-sm">Search</button>
        </form>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Hotel</th><th>Tables</th><th>Active now</th><th></th></tr></thead>
            <tbody>
                @forelse($hotels as $hotel)
                    <tr>
                        <td>{{ $hotel->name }}</td>
                        <td>{{ $hotel->tables_count }}</td>
                        <td>{{ $hotel->active_tables_count }}</td>
                        <td class="row-actions">
                            <a href="{{ route('superadmin.hotels.tables', $hotel) }}" class="btn btn-outline btn-sm">View tables &amp; QR codes</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:var(--navy-soft);">No hotels match your search.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $hotels->links() }}
@endsection
