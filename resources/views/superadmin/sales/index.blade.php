@extends('layouts.admin')

@section('title', 'Sales — Super Admin')
@section('page-title', 'Sales across every hotel')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All orders</h3>
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
            <thead><tr><th>Hotel</th><th>Order #</th><th>Table</th><th>Member</th><th>Total</th><th>Status</th><th>Placed</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->table->hotel->name }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>Table {{ $order->table->table_number }}</td>
                        <td>{{ $order->member_no }}</td>
                        <td>₹{{ number_format($order->total_amount, 0) }}</td>
                        <td><span class="badge badge-{{ $order->status->value === 'cancelled' ? 'cancelled' : ($order->status->value === 'served' ? 'served' : 'preparing') }}">{{ $order->status->value }}</span></td>
                        <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="color:var(--navy-soft);">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $orders->links() }}
@endsection
