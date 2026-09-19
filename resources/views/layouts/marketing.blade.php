<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Aries Restro — Contactless Table Ordering for Restaurants')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Aries Restro turns any table into a contactless ordering counter — customers scan, order, and pay from their own phone. No app, no login.">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@stack('styles')
</head>
<body class="marketing-body">
@yield('content')
<div class="toast" id="toast"></div>
<script src="{{ asset('js/shared.js') }}"></script>
@stack('scripts')
</body>
</html>
