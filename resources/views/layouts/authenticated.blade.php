{{-- resources/views/layouts/authenticated.blade.php --}}
@extends('layouts.base')

@push('styles')
    @vite(['resources/css/blade/base.css'])
@endpush

@push('scripts')
    @vite(['resources/js/blade/base.js'])
    @vite(['resources/js/session-timeout.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.userData = {
                id:       '{{ Auth::id() }}',
                name:     '{{ Auth::user()->name ?? '' }}',
                avatar:   '{{ Auth::user()->avatar ?? '' }}',
                type:     '{{ Auth::user()->type ?? '' }}',
                verified: '{{ (Auth::user()->verified ?? false) ? '1' : '0' }}'
            };

            window.pusherConfig = {
                key:     '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
            };
        });
    </script>
@endpush

@section('body')
    @include('partials.dashboard-sidebar')

    <div class="@yield('page-class', 'main-content')" id="@yield('page-id', 'mainContent')">
        {{-- Only show header if not on chat page or if explicitly enabled --}}
        @if(!isset($hideHeader) && ($withHeader ?? true))
            @include('partials.dashboard-header')
        @endif

        <main @if(isset($fullPage) && $fullPage) style="padding: 0; height: 100%;" @endif>
            @yield('content')
        </main>
    </div>

    <button class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
@endsection
