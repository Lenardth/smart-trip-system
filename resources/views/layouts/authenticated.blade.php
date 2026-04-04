@extends('layouts.base')

@push('body-attrs')
class="dashboard-page"
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.userData = {
                id:       '{{ Auth::id() }}',
                name:     '{{ Auth::user()->name ?? '' }}',
                avatar:   '{{ Auth::user()->avatar ?? '' }}',
                type:     '{{ Auth::user()->user_type ?? '' }}',
                verified: '{{ Auth::user()->hasVerifiedEmail() ? '1' : '0' }}'
            };
            window.pusherConfig = {
                key:     '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
            };
        });
    </script>
    @stack('scripts_body')
@endpush

@section('body')
    @include('partials.dashboard-sidebar')

    <div class="@yield('page-class', 'main-content')" id="@yield('page-id', 'mainContent')">
        @if(!isset($hideHeader) && ($withHeader ?? true))
            @include('partials.dashboard-header')
        @endif

        <main @if(isset($fullPage) && $fullPage) style="padding:0;height:100%;" @endif>
            @yield('content')
        </main>
    </div>

    <button class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
@endsection
