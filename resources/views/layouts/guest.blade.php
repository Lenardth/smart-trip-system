@extends('layouts.base')

@section('body')
<div class="auth-page-wrap">

    <div class="auth-brand-panel">
        <a href="/" class="auth-brand-logo">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking">
        </a>
        <h1 class="auth-brand-title">Smart <span>Booking</span></h1>
        <p class="auth-brand-sub">AI-powered travel planning for every kind of trip.</p>
        <div class="auth-brand-features">
            <div class="auth-feature"><i class="fas fa-magic"></i> AI trip planning &amp; scoring</div>
            <div class="auth-feature"><i class="fas fa-plane"></i> Search &amp; book flights</div>
            <div class="auth-feature"><i class="fas fa-route"></i> Itineraries &amp; PDF export</div>
            <div class="auth-feature"><i class="fas fa-map-marked-alt"></i> Discovery &amp; saved places</div>
            <div class="auth-feature"><i class="fas fa-coins"></i> Multi-currency &amp; receipts</div>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-form-inner">
            {{ $slot }}
        </div>
    </div>

</div>
@endsection
