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
@auth
    @if(auth()->user()->role === \App\Enums\UserRole::Waiter)
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.6.0/dist/web/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.5.0/dist/echo.iife.js"></script>
        <script>
            window.Pusher = Pusher;
            window.Echo = new Echo.default({
                broadcaster: 'reverb',
                key: @json(config('broadcasting.connections.reverb.key')),
                wsHost: @json(config('broadcasting.connections.reverb.options.host')),
                wsPort: {{ (int) config('broadcasting.connections.reverb.options.port', 80) }},
                wssPort: {{ (int) config('broadcasting.connections.reverb.options.port', 443) }},
                forceTLS: {{ config('broadcasting.connections.reverb.options.scheme') === 'https' ? 'true' : 'false' }},
                enabledTransports: ['ws', 'wss'],
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
            });
        </script>
    @endif
@endauth
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
@auth
    @if(auth()->user()->role === \App\Enums\UserRole::Waiter)
        <script src="{{ asset('js/waiter-bell.js') }}"></script>
    @endif
@endauth
@stack('scripts')
</body>
</html>
