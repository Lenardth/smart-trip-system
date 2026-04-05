@extends('layouts.base')

@section('body')
    <div style="min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--cream);padding:40px 20px;">

        <a href="/" style="margin-bottom:24px;display:flex;align-items:center;gap:10px;text-decoration:none;">
            <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" style="height:52px;width:auto;">
            <span style="font-size:22px;font-weight:700;font-family:'Georgia',serif;letter-spacing:.5px;color:var(--deep);">Smart <span style="color:var(--gold);">Booking</span></span>
        </a>

        <div style="width:100%;max-width:460px;background:var(--card-bg);border-radius:var(--radius);box-shadow:var(--shadow-lg);padding:36px;border:1px solid var(--border);">
            {{ $slot }}
        </div>

    </div>
@endsection
