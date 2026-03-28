{{-- resources/views/layouts/app-no-nav.blade.php --}}
@extends('layouts.base')

@push('styles')
    @vite(['resources/css/app.css', 'resources/css/blade/base.css'])
@endpush

@section('body')
    <div class="min-h-screen bg-gray-100">
        <header style="background: #fff; border-bottom: 1px solid #e5e7eb; padding: 12px 24px; display: flex; align-items: center; gap: 10px;">
            <a href="/" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" class="logo">
                <span class="logo-text">Smart Booking</span>
            </a>
        </header>

        @yield('content')
    </div>
@endsection
