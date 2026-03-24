<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — Smart Booking</title>
    @vite([
        'resources/css/blade/base.css',
        'resources/css/blade/auth/forgot-password.css'
    ])

</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
            </div>
            <h1 class="auth-title">Forgot Password?</h1>
            <p class="auth-subtitle">No problem. Just let us know your email address and we will send you a password reset link.</p>
            
            @if (session('status'))
                <div class="success-message">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="auth-input @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        placeholder="your@email.com"
                    >
                    @error('email')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-envelope"></i> Email Password Reset Link
                </button>
            </form>

            <div class="auth-divider">
                <span>Remember your password?</span>
            </div>

            <a href="{{ route('login') }}" class="auth-link">
                <i class="fas fa-sign-in-alt"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>
