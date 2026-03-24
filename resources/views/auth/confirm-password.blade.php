<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirm Password — Smart Booking</title>
    @vite([
        'resources/css/blade/base.css',
        'resources/css/blade/auth/confirm-password.css'
    ])

</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
            </div>
            <h1 class="auth-title">Confirm Password</h1>
            <p class="auth-subtitle">This is a secure area. Please confirm your password before continuing.</p>
            
            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="info-box">
                <i class="fas fa-shield-alt"></i>
                For your security, please re-enter your password to continue.
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="auth-input @error('password') is-invalid @enderror" 
                        required 
                        autofocus
                        placeholder="Enter your password"
                    >
                    @error('password')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-check"></i> Confirm
                </button>
            </form>
        </div>
    </div>
</body>
</html>
