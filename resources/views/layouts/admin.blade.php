<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Aries Restro Admin')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@stack('styles')
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        @include(auth()->user()->role->value === 'super_admin' ? 'partials.sidebar-superadmin' : 'partials.sidebar-hoteladmin')
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle" id="sidebar-toggle">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="topbar-actions">
                <div class="who"><b>{{ auth()->user()->name }}</b>{{ ucfirst(str_replace('_', ' ', auth()->user()->role->value)) }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Log out</button>
                </form>
            </div>
        </header>
        <main class="admin-content">
            @if (session('status'))
                <div class="card" style="border-color:var(--green); background:var(--green-tint); color:var(--green); margin-bottom:16px;">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<div class="toast" id="toast"></div>
<script src="{{ asset('js/shared.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
