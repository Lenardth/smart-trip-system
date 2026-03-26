<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Booking')</title>

    @vite([
        'resources/css/blade/base.css',
        'resources/js/blade/base.js',
        'resources/js/session-timeout.js'
    ])

    @stack('styles')
</head>
<body
    data-user-id="{{ Auth::id() }}"
    data-user-name="{{ Auth::user()->name ?? '' }}"
    data-user-avatar="{{ Auth::user()->avatar ?? '' }}"
    data-user-type="{{ Auth::user()->type ?? '' }}"
    data-user-verified="{{ Auth::user()->verified ? '1' : '0' }}"
    data-pusher-key="{{ config('broadcasting.connections.pusher.key') }}"
    data-pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster') }}"
>
    @include('partials.dashboard-sidebar')

    <div class="main-content">
        @include('partials.dashboard-header')

        <main>
            @yield('content')
        </main>
    </div>

    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    @stack('scripts')
</body>
</html>
