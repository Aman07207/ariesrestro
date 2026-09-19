@extends('layouts.admin')

@section('title', 'Demo Requests — Super Admin')
@section('page-title', 'Demo requests')

@section('content')
<div class="panel">
    <div class="panel-head"><h3>Inbound leads from the Home page</h3></div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Hotel / Café</th><th>Contact</th><th>Message</th><th>Status</th><th>Received</th><th></th></tr></thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td>{{ $req->name }}</td>
                        <td>{{ $req->hotel_name }}</td>
                        <td>{{ $req->phone }}<br><span class="sub">{{ $req->email }}</span></td>
                        <td style="max-width:240px;">{{ $req->message ?? '—' }}</td>
                        <td><span class="badge badge-{{ $req->status->value === 'new' ? 'preparing' : ($req->status->value === 'contacted' ? 'ready' : 'cancelled') }}">{{ $req->status->value }}</span></td>
                        <td>{{ $req->created_at->diffForHumans() }}</td>
                        <td class="row-actions">
                            @if($req->status->value === 'new')
                                <form method="POST" action="{{ route('superadmin.demo-requests.contacted', $req) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Mark contacted</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="color:var(--navy-soft);">No demo requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $requests->links() }}
@endsection
