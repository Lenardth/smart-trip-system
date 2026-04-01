@extends('layouts.base')

@section('title', 'Reset Password — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/base.css', 'resources/css/blade/auth/reset-password.css'])
@endpush

@section('body')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
        </div>
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Enter your new password below.</p>

        @if($errors->any())
            <div class="error-message">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                    class="auth-input @error('email') is-invalid @enderror"
                    value="{{ old('email', $request->email) }}" required autofocus placeholder="your@email.com">
                @error('email')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password"
                    class="auth-input @error('password') is-invalid @enderror"
                    required placeholder="At least 8 characters">
                @error('password')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="auth-input" required placeholder="Re-enter your password">
            </div>

            <button type="submit" class="auth-btn">
                <i class="fas fa-key"></i> Reset Password
            </button>
        </form>
    </div>
</div>
@endsection
