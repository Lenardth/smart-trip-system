<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Smart Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
     @vite(['resources/css/app.css', 'resources/css/pages/login.css', 'resources/js/pages/login.js'])
     @vite(['resources/css/pages/login.css', 'resources/js/pages/login.js'])
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">

            <div class="auth-logo">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
                <span class="auth-logo-text">Smart Booking</span>
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

            <form method="POST" action="{{ route('login') }}" id="loginForm">
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
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="auth-input @error('password') is-invalid @enderror"
                            required
                            placeholder="Enter your password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="auth-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Log In
                </button>
            </form>

            <div class="auth-divider">
                <span>Don't have an account?</span>
            </div>

            <a href="{{ route('register') }}" class="auth-link">
                <i class="fas fa-user-plus"></i> Create New Account
            </a>

            <a href="/" class="auth-link">
                <i class="fas fa-home"></i> Back to Home
            </a>

        </div>
    </div>

    <script src="{{ asset('js/pages/login.js') }}"></script>
</body>
</html>
