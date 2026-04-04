@extends('layouts.base')

@push('styles')
    @vite(['resources/css/blade/auth/login.css'])
@endpush

@section('body')
    <div style="min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--cream);padding:40px 20px;">

        <a href="/" style="margin-bottom:24px;display:flex;align-items:center;gap:10px;text-decoration:none;">
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking" class="logo" style="height:60px;">
            <span class="logo-text">Smart Booking</span>
        </a>

        <div style="width:100%;max-width:440px;background:var(--card-bg);border-radius:var(--radius);box-shadow:var(--shadow-lg);padding:36px;border:1px solid var(--border);">
            {{ $slot }}
        </div>

    </div>
@endsection
