@extends('layouts.admin')

@section('title', 'Staff — Super Admin')
@section('page-title', 'Staff across all hotels')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h3>All staff</h3>
        <a href="{{ route('superadmin.staff.create') }}" class="btn btn-primary btn-sm">+ Add staff</a>
    </div>
    <div class="panel-body">
        <form method="GET" class="form-grid" style="margin-bottom:14px; align-items:end;">
            <div>
                <label class="flabel">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, employee ID or email">
            </div>
            <div>
                <label class="flabel">Hotel</label>
                <select name="hotel" onchange="this.form.submit()">
                    <option value="">All hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" @selected((string) request('hotel') === (string) $h->id)>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="flabel">Role</label>
                <select name="role" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ ucwords(str_replace('_', ' ', $r->value)) }}</option>
                    @endforeach
                </select>
            </div>
            <div><button type="submit" class="btn btn-outline btn-sm">Search</button></div>
        </form>
    </div>
    <div class="panel-body table-scroll" style="padding-top:0;">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Hotel</th><th>Employee ID</th><th>Role</th><th>Phone</th><th></th></tr></thead>
            <tbody>
                @forelse($staff as $member)
                    <tr>
                        <td>{{ $member->name }}<div class="sub">{{ $member->email }}</div></td>
                        <td>{{ $member->hotel->name }}</td>
                        <td>{{ $member->employee_id }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $member->role->value)) }}</td>
                        <td>{{ $member->phone ?? '—' }}</td>
                        <td class="row-actions">
                            <a href="{{ route('superadmin.staff.edit', $member) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('superadmin.staff.destroy', $member) }}" onsubmit="return confirm('Remove {{ $member->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red); border-color:var(--red-tint);">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:var(--navy-soft);">No staff match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $staff->links() }}
@endsection
