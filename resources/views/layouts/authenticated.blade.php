{{-- resources/views/layouts/authenticated.blade.php --}}
@extends('layouts.base')

@push('styles')
    @vite(['resources/css/blade/base.css'])
    @vite(['resources/js/blade/base.js'])
@endpush

@push('scripts')
    @vite(['resources/js/session-timeout.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.userData = {
                id: '{{ Auth::id() }}',
                name: '{{ Auth::user()->name ?? '' }}',
                avatar: '{{ Auth::user()->avatar ?? '' }}',
                type: '{{ Auth::user()->type ?? '' }}',
                verified: '{{ Auth::user()->verified ? '1' : '0' }}'
            };

            window.pusherConfig = {
                key: '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
            };
        });
    </script>
@endpush

@section('body')
    @include('partials.dashboard-sidebar')

    <div class="@yield('page-class', 'main-content')" id="@yield('page-id', 'mainContent')">
        @if($withHeader ?? true)
            @include('partials.dashboard-header')
        @endif

        <main>
            @yield('content')
        </main>
    </div>

    <button class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
@endsection
