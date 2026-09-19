@extends('layouts.app')

@section('title', 'Your Cart — Aries Restro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <a href="{{ route('customer.menu') }}" class="iconbtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></a>
        <div><h2>Your cart</h2><div class="sub">Member {{ $memberNo }} · Table {{ $table->table_number }}</div></div>
    </div>
</div>

<div class="content" id="cart-content"></div>

@endsection

@push('scripts')
<script>
window.ARIES_PLACE_ORDER_URL = @json(route('customer.track'));
window.ARIES_PLACE_ORDER_API_URL = @json(route('customer.order.place'));
window.ARIES_MENU_URL = @json(route('customer.menu'));
</script>
<script src="{{ asset('js/customer.js') }}"></script>
@endpush
