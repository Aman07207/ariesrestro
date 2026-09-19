@extends('layouts.admin')

@section('title', 'Table '.$table->table_number.' QR — Hotel Admin')
@section('page-title', 'Table '.$table->table_number.' QR code')

@push('styles')
<style>
@media print {
    .admin-sidebar, .admin-topbar, .no-print { display: none !important; }
    .admin-main { margin-left: 0 !important; }
    .admin-content { padding: 0 !important; }
}
</style>
@endpush

@section('content')
<div class="panel" style="max-width:420px; margin:0 auto;">
    <div class="panel-body" style="text-align:center;">
        <h3 style="margin-bottom:4px;">Table {{ $table->table_number }}</h3>
        <p style="color:var(--navy-soft); font-size:12.5px; margin-bottom:18px;">Print this and place it on the table. Customers scan it to open the menu — no app, no login.</p>
        <img src="{{ route('hoteladmin.tables.qr-image', $table) }}" alt="QR code for Table {{ $table->table_number }}" style="width:260px; height:260px; margin:0 auto;">
        <p style="word-break:break-all; font-size:11px; color:var(--navy-soft); margin-top:16px;">{{ $scanUrl }}</p>
        <div class="no-print" style="display:flex; gap:10px; margin-top:20px;">
            <a href="{{ route('hoteladmin.tables.index') }}" class="btn btn-outline">Back to tables</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>
</div>
@endsection
