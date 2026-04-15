<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Smart Booking'))</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/js/blade/shared/currency.js'])
    @stack('styles')
</head>
<body @stack('body-attrs')>

    @yield('body')

    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    @stack('scripts')
    <script>
    (function initCurrencySwitchers() {
        function tryInit() {
            if (typeof window.Currency === 'undefined') { setTimeout(tryInit, 100); return; }
            ['navCurrencySlot','dashCurrencySlot'].forEach(function(id) {
                if (document.getElementById(id)) window.Currency.buildSwitcher(id);
            });
        }
        if (document.readyState !== 'loading') tryInit();
        else document.addEventListener('DOMContentLoaded', tryInit);
    })();
    </script>

</body>
</html>