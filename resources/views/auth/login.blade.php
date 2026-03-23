<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Smart Booking</title>
    @vite([
        'resources/css/blade/base.css',
        'resources/css/blade/auth/login.css'
    ])

</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
            </div>
            <h1 class="auth-title">Welcome Back</h1>

            @if (session('success'))
                <div class="success-message">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
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

                <div class="input-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        required
                        placeholder="Enter your password"
                    >
                    @error('password')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" style="margin:0;font-weight:normal;cursor:pointer;">Remember me</label>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i> Log In
                </button>
            </form>

            <div class="auth-divider">
                <span>Don't have an account?</span>
            </div>

            <a href="{{ route('register') }}" class="auth-link">
                <i class="fas fa-user-plus"></i> Create New Account
            </a>

            <a href="/" class="auth-link" style="margin-top:10px;">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>
