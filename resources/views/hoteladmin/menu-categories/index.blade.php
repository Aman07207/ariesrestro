@extends('layouts.admin')

@section('title', 'Menu Categories — Hotel Admin')
@section('page-title', 'Menu categories')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All categories</h3>
        <a href="{{ route('hoteladmin.menu-categories.create') }}" class="btn btn-primary btn-sm">+ Add category</a>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Type</th><th>Order</th><th>Items</th><th></th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ ucfirst($category->type) }}</td>
                        <td>{{ $category->display_order }}</td>
                        <td>{{ $category->menu_items_count }}</td>
                        <td class="row-actions">
                            <a href="{{ route('hoteladmin.menu-categories.edit', $category) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('hoteladmin.menu-categories.destroy', $category) }}" onsubmit="return confirm('Remove {{ $category->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:var(--navy-soft);">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
