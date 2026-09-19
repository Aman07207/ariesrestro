@extends('layouts.admin')

@section('title', 'Payment Settings — Super Admin')
@section('page-title', 'Payment settings')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>Razorpay settings per hotel</h3>
        <a href="{{ route('superadmin.payment-settings.create') }}" class="btn btn-primary btn-sm">+ Add settings</a>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Hotel</th><th>Linked account</th><th>Commission</th><th></th></tr></thead>
            <tbody>
                @forelse($settings as $setting)
                    <tr>
                        <td>{{ $setting->hotel->name }}</td>
                        <td>{{ $setting->razorpay_linked_account_id ?? '—' }}</td>
                        <td>{{ $setting->commission_percent }}%</td>
                        <td class="row-actions">
                            <a href="{{ route('superadmin.payment-settings.edit', $setting) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('superadmin.payment-settings.destroy', $setting) }}" onsubmit="return confirm('Remove these payment settings?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:var(--navy-soft);">No payment settings configured yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $settings->links() }}
@endsection
