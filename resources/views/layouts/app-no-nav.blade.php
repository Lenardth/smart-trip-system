<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Smart Booking'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    @vite([
        'resources/css/app.css',
        'resources/css/blade/base.css'
    ])
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
