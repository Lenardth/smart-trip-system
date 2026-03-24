<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email — Smart Booking</title>
    @vite([
        'resources/css/blade/base.css',
        'resources/css/blade/auth/verify-email.css'
    ])

</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
            </div>
            <h1 class="auth-title">Verify Your Email</h1>
            <p class="auth-subtitle">Thanks for signing up! Please verify your email address by clicking the link we sent to your inbox.</p>
            
            @if (session('status') == 'verification-link-sent')
                <div class="success-message">
                    A new verification link has been sent to your email address!
                </div>
            @endif

            <div class="info-box">
                <i class="fas fa-envelope-open"></i>
                Check your email inbox for the verification link. Don't forget to check your spam folder!
            </div>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-paper-plane"></i> Resend Verification Email
                </button>
            </form>

            <div class="auth-divider">
                <span>Or</span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="auth-link" style="border: none; background: transparent; width: 100%; cursor: pointer;">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </button>
            </form>
        </div>
    </div>
</body>
</html>
