@extends('layouts.base')

@section('body')
<div class="auth-page-wrap">

    {{-- Left panel — branding --}}
    <div class="auth-brand-panel">
        <a href="/" class="auth-brand-logo">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking">
        </a>
        <h1 class="auth-brand-title">Smart <span>Booking</span></h1>
        <p class="auth-brand-sub">AI-powered travel planning for every kind of trip.</p>
        <div class="auth-brand-features">
            <div class="auth-feature"><i class="fas fa-route"></i> Plan trips with AI</div>
            <div class="auth-feature"><i class="fas fa-plane"></i> Search & book flights</div>
            <div class="auth-feature"><i class="fas fa-heart"></i> Save your dream destinations</div>
            <div class="auth-feature"><i class="fas fa-users"></i> Connect with travellers</div>
        </div>
    </div>

    {{-- Right panel — form --}}
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            {{ $slot }}
        </div>
    </div>

</div>
@endsection
