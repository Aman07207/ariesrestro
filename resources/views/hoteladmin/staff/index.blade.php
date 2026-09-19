@extends('layouts.admin')

@section('title', 'Staff — Hotel Admin')
@section('page-title', 'Staff')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All staff</h3>
        <a href="{{ route('hoteladmin.staff.create') }}" class="btn btn-primary btn-sm">+ Add staff</a>
    </div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Employee ID</th><th>Role</th><th>Phone</th><th></th></tr></thead>
            <tbody>
                @forelse($staff as $member)
                    <tr>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->employee_id }}</td>
                        <td>{{ ucfirst($member->role->value) }}</td>
                        <td>{{ $member->phone ?? '—' }}</td>
                        <td class="row-actions">
                            <a href="{{ route('hoteladmin.staff.edit', $member) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('hoteladmin.staff.destroy', $member) }}" onsubmit="return confirm('Remove {{ $member->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:var(--navy-soft);">No staff yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $staff->links() }}
@endsection
