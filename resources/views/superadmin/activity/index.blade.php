@extends('layouts.admin')

@section('title', 'Activity Log — Super Admin')
@section('page-title', 'Activity log')

@section('content')
<div class="panel">
    <div class="panel-head"><h3>All platform activity</h3></div>
    <div class="panel-body table-scroll">
        <table class="admin-table">
            <thead><tr><th>When</th><th>Log</th><th>Event</th><th>Description</th><th>Subject</th><th>Causer</th></tr></thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('d M Y, H:i') }}</td>
                        <td>{{ $activity->log_name }}</td>
                        <td>{{ $activity->event ?? '—' }}</td>
                        <td>{{ $activity->description }}</td>
                        <td>{{ $activity->subject ? class_basename($activity->subject_type).' #'.$activity->subject_id : '—' }}</td>
                        <td>{{ $activity->causer?->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:var(--navy-soft);">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $activities->links() }}
@endsection
