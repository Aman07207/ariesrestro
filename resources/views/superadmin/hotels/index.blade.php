@extends('layouts.admin')

@section('title', 'Hotels — Super Admin')
@section('page-title', 'Hotels')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All hotels</h3>
        <a href="{{ route('superadmin.hotels.create') }}" class="btn btn-primary btn-sm">+ Add hotel</a>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th></th><th>Name</th><th>Plan</th><th>Status</th><th>Owner</th><th>GST</th><th>VAT</th><th>Service chg.</th><th></th></tr></thead>
            <tbody>
                @forelse($hotels as $hotel)
                    <tr>
                        <td>
                            @if($hotel->logo)
                                <img src="{{ $hotel->logoUrl() }}" alt="" style="width:28px; height:28px; border-radius:6px; object-fit:cover;">
                            @endif
                        </td>
                        <td>{{ $hotel->name }}{{ $hotel->is_luxury_hotel ? ' 🌟' : '' }}</td>
                        <td>{{ ucfirst($hotel->subscription_plan) }}</td>
                        <td><span class="badge badge-{{ $hotel->status->value === 'active' ? 'ready' : 'cancelled' }}">{{ $hotel->status->value }}</span></td>
                        <td>{{ $hotel->owner_name ?? '—' }}</td>
                        <td>{{ $hotel->gst_rate }}%</td>
                        <td>{{ $hotel->vat_rate !== null ? $hotel->vat_rate.'%' : '—' }}</td>
                        <td>{{ $hotel->default_service_charge_percent !== null ? $hotel->default_service_charge_percent.'%' : '—' }}</td>
                        <td class="row-actions">
                            <a href="{{ route('superadmin.hotels.edit', $hotel) }}" class="btn btn-outline btn-sm">Edit</a>
                            @if($hotel->status->value === 'active')
                                <form method="POST" action="{{ route('superadmin.hotels.destroy', $hotel) }}" onsubmit="return confirm('Deactivate {{ $hotel->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Deactivate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="color:var(--navy-soft);">No hotels yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $hotels->links() }}
@endsection
