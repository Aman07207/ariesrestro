@extends('layouts.admin')

@section('title', 'Subscriptions — Super Admin')
@section('page-title', 'Subscriptions')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All subscriptions</h3>
        <a href="{{ route('superadmin.subscriptions.create') }}" class="btn btn-primary btn-sm">+ Add subscription</a>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Hotel</th><th>Plan</th><th>Cycle</th><th>Price</th><th>Ends</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    <tr>
                        <td>{{ $sub->hotel->name }}</td>
                        <td>{{ ucfirst($sub->plan) }}</td>
                        <td>{{ ucfirst($sub->billing_cycle) }}</td>
                        <td>₹{{ number_format($sub->price, 0) }}</td>
                        <td>{{ $sub->end_date?->format('d M Y') ?? '—' }}</td>
                        <td><span class="badge badge-{{ $sub->status->value === 'active' ? 'ready' : 'cancelled' }}">{{ $sub->status->value }}</span></td>
                        <td class="row-actions">
                            <a href="{{ route('superadmin.subscriptions.edit', $sub) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('superadmin.subscriptions.destroy', $sub) }}" onsubmit="return confirm('Remove this subscription?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="color:var(--navy-soft);">No subscriptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $subscriptions->links() }}
@endsection
