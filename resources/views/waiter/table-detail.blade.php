@extends('layouts.app')

@section('title', 'Table '.$table->table_number.' — Aries Restro Waiter')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/waiter.css') }}">
@endpush

@section('content')
<livewire:waiter.table-detail :table="$table" />
@endsection

@push('modals')
<div class="overlay center-modal" id="overlay-cancel"></div>
<div class="overlay center-modal" id="overlay-transfer"></div>
@endpush

@push('scripts')
<script>
window.ARIES_TABLES_URL = @json(route('waiter.tables'));
window.ARIES_SETTLE_URL = @json(route('waiter.tables.settle', $table));
window.ARIES_CLOSE_TABLE_URL = @json(route('waiter.tables.close', $table));
window.ARIES_ORDER_ITEMS_URL_BASE = @json(url('/waiter/order-items'));
</script>
<script src="{{ asset('js/waiter.js') }}"></script>
@endpush
