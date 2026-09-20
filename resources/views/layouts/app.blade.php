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
@livewireStyles
@php
    // Realtime is for the kitchen/floor staff (private channels) and for the customer's
    // tracking page (public channel); admins and other pages don't need a socket.
    $realtime = auth()->check()
        ? in_array(auth()->user()->role, [\App\Enums\UserRole::Waiter, \App\Enums\UserRole::Chef], true)
        : isset($table);
@endphp
@if($realtime)
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.6.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.5.0/dist/echo.iife.js"></script>
    <script>
        window.ARIES_RT = {
            key: @json(config('broadcasting.connections.reverb.key')),
            host: @json(config('broadcasting.connections.reverb.options.host')),
            port: {{ (int) config('broadcasting.connections.reverb.options.port', 8080) }},
            scheme: @json(config('broadcasting.connections.reverb.options.scheme', 'http')),
            authEndpoint: @json(url('/broadcasting/auth')),
        };
    </script>
    <script src="{{ asset('js/realtime.js') }}"></script>
@endif
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
@livewireScripts
@if($realtime)
    <div id="aries-rt"><span id="aries-rt-dot" data-state="connecting"></span><button type="button" id="aries-rt-sound"></button></div>
    <script src="{{ asset('js/notify.js') }}"></script>
@endif
@stack('scripts')
</body>
</html>
