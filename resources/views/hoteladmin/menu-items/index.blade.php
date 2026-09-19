@extends('layouts.admin')

@section('title', 'Menu Items — Hotel Admin')
@section('page-title', 'Menu items')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All menu items</h3>
        <a href="{{ route('hoteladmin.menu-items.create') }}" class="btn btn-primary btn-sm">+ Add item</a>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Veg</th><th>Available</th><th></th></tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category->name }}</td>
                        <td>₹{{ number_format($item->price, 0) }}</td>
                        <td>{{ $item->veg_type === 'veg' ? 'Veg' : 'Non-veg' }}</td>
                        <td><span class="badge badge-{{ $item->is_available ? 'ready' : 'cancelled' }}">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span></td>
                        <td class="row-actions">
                            <a href="{{ route('hoteladmin.menu-items.edit', $item) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('hoteladmin.menu-items.destroy', $item) }}" onsubmit="return confirm('Remove {{ $item->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:var(--navy-soft);">No menu items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $items->links() }}
@endsection
