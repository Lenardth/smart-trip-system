@extends('layouts.base')

@section('title', 'Login — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/base.css', 'resources/css/blade/auth/login.css'])
@endpush

@push('scripts')
    @vite(['resources/js/blade/login.js'])
@endpush

@section('body')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
        </div>
        <h1 class="auth-title">Welcome Back</h1>

        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-message">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                    class="auth-input @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required autofocus placeholder="your@email.com">
                @error('email')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        required placeholder="Enter your password">
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Toggle password visibility">
                        <i id="toggleIcon" class="fas fa-eye-slash"></i>
                    </button>
                </div>
                @error('password')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" style="margin:0;font-weight:normal;cursor:pointer;">Remember me</label>
            </div>

            <button type="submit" class="auth-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Log In
            </button>
        </form>

        <div class="auth-divider"><span>Don't have an account?</span></div>
        <a href="{{ route('register') }}" class="auth-link"><i class="fas fa-user-plus"></i> Create New Account</a>
        <a href="/" class="auth-link" style="margin-top:10px;"><i class="fas fa-home"></i> Back to Home</a>
    </div>
</div>
@endsection
