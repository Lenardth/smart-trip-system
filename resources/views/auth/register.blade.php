@extends('layouts.base')

@section('title', 'Register — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/base.css', 'resources/css/blade/auth/register.css'])
@endpush

@push('scripts')
    @vite(['resources/js/blade/auth/register.js'])
@endpush

@section('body')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
        </div>
        <h1 class="auth-title">Create Account</h1>

        @if($errors->any())
            <div class="error-message">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf
            <div class="input-group">
                <label>I am registering as:</label>
                <div class="user-type-selector">
                    <div class="type-option">
                        <input type="radio" name="user_type" value="user" id="type_user"
                            {{ old('user_type', 'user') == 'user' ? 'checked' : '' }}
                            onchange="toggleAgencyFields()">
                        <label for="type_user" class="type-card">
                            <div class="type-icon"><i class="fas fa-user"></i></div>
                            <h4>Normal User</h4>
                            <p>Book trips & explore</p>
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="user_type" value="agency" id="type_agency"
                            {{ old('user_type') == 'agency' ? 'checked' : '' }}
                            onchange="toggleAgencyFields()">
                        <label for="type_agency" class="type-card">
                            <div class="type-icon"><i class="fas fa-building"></i></div>
                            <h4>Travel Agency</h4>
                            <p>Manage flights & bookings</p>
                        </label>
                    </div>
                </div>
                @error('user_type')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                    class="auth-input @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" required autofocus placeholder="John Doe">
                @error('name')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="agency-fields {{ old('user_type') == 'agency' ? 'show' : '' }}" id="agency-fields">
                <div class="input-group">
                    <label for="agency_name">Agency Name</label>
                    <input type="text" id="agency_name" name="agency_name"
                        class="auth-input @error('agency_name') is-invalid @enderror"
                        value="{{ old('agency_name') }}" placeholder="Travel Co. Ltd.">
                    @error('agency_name')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                    class="auth-input @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required placeholder="your@email.com">
                @error('email')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                    class="auth-input @error('password') is-invalid @enderror"
                    required placeholder="Minimum 8 characters">
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
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-divider"><span>Already have an account?</span></div>
        <a href="{{ route('login') }}" class="auth-link"><i class="fas fa-sign-in-alt"></i> Log In</a>
        <a href="/" class="auth-link" style="margin-top:10px;"><i class="fas fa-home"></i> Back to Home</a>
    </div>
</div>
@endsection
