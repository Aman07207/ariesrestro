<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Aries Restro')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@stack('styles')
</head>
<body>
<div class="screen">
    @yield('content')
</div>
<div class="toast" id="toast"></div>
@stack('modals')
@isset($table)
<script>
window.ARIES_TABLE_UUID = @json($table->table_uuid);
window.ARIES_CALL_WAITER_URL = @json(route('customer.call-waiter'));
</script>
@endisset
<script src="{{ asset('js/shared.js') }}"></script>
@auth
    @if(auth()->user()->role === \App\Enums\UserRole::Waiter)
        <script>window.ARIES_WAITER_CALLS_PENDING_URL = @json(route('waiter.calls.pending'));</script>
        <script src="{{ asset('js/waiter-alerts.js') }}"></script>
    @endif
@endauth
@stack('scripts')
</body>
</html>
