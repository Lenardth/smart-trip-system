<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self';">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    <title>Login — Smart Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', 'Helvetica Neue', 'Georgia', serif;
            background: linear-gradient(135deg, #3b1f2b 0%, #4d2a3a 50%, #c9a96e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }

        .auth-page {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: rgba(255, 248, 242, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo i {
            font-size: 64px;
            background: linear-gradient(135deg, #3b1f2b, #c9a96e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-title {
            text-align: center;
            color: #3b1f2b;
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .auth-subtitle {
            text-align: center;
            color: #6b5b4f;
            font-size: 15px;
            margin-bottom: 35px;
        }

        .error-message, .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
            line-height: 1.5;
            animation: shake 0.3s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .success-message, .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #c3e6cb;
            font-size: 14px;
            line-height: 1.5;
        }

        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #3b1f2b;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .auth-input {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 2px solid #e2d5c7;
            border-radius: 12px;
            font-size: 15px;
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', sans-serif;
            transition: all 0.3s ease;
            background: white;
            color: #2c2c2c;
        }

        .auth-input:focus {
            outline: none;
            border-color: #c9a96e;
            box-shadow: 0 0 0 4px rgba(201, 169, 110, 0.1);
            transform: translateY(-2px);
        }

        .auth-input.is-invalid {
            border-color: #f5c6cb;
            background: #fff5f5;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 42px;
            color: #6b5b4f;
            font-size: 16px;
            pointer-events: none;
        }

        .input-error {
            color: #721c24;
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .input-error i {
            font-size: 12px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .remember-me-check {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #3b1f2b;
        }

        .remember-me input[type="checkbox"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #c9a96e;
        }

        .remember-me label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
            user-select: none;
        }

        .forgot-link {
            color: #c9a96e;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #b8955a;
        }

        .auth-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #3b1f2b 0%, #4d2a3a 50%, #c9a96e 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', sans-serif;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(59, 31, 43, 0.3);
            position: relative;
            overflow: hidden;
        }

        .auth-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .auth-btn:hover::before {
            left: 100%;
        }

        .auth-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(59, 31, 43, 0.4);
        }

        .auth-btn:active {
            transform: translateY(-1px);
        }

        .auth-btn i {
            margin-right: 8px;
        }

        .auth-divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #d4c4b0, transparent);
        }

        .auth-divider span {
            background: #fff8f2;
            padding: 0 20px;
            position: relative;
            color: #6b5b4f;
            font-size: 13px;
            font-weight: 500;
        }

        .auth-link {
            display: block;
            text-align: center;
            padding: 14px;
            color: #3b1f2b;
            text-decoration: none;
            border: 2px solid #e2d5c7;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 600;
            margin-top: 12px;
            background: white;
        }

        .auth-link:hover {
            background: #3b1f2b;
            color: white;
            border-color: #3b1f2b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 31, 43, 0.2);
        }

        .auth-link i {
            margin-right: 8px;
        }

        /* Loading state */
        .auth-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 520px) {
            .auth-card {
                padding: 40px 30px;
            }

            .auth-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-plane-departure"></i>
            </div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to continue to Smart Booking</p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if (session('success'))
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <!-- Email Address -->
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <i class="fas fa-envelope input-icon"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="auth-input @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="Enter your email"
                        maxlength="255"
                    >
                    @error('email')
                        <div class="input-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="input-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        minlength="8"
                    >
                    @error('password')
                        <div class="input-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="remember-me">
                    <div class="remember-me-check">
                        <input
                            type="checkbox"
                            id="remember_me"
                            name="remember"
                        >
                        <label for="remember_me">Remember me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="auth-btn" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-divider">
                <span>New to Smart Booking?</span>
            </div>

            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="auth-link">
                    <i class="fas fa-user-plus"></i> Create New Account
                </a>
            @endif

            <a href="/" class="auth-link">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        'use strict';

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Basic client-side validation
            if (!email || !password) {
                e.preventDefault();
                return false;
            }

            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Signing in...';
        });

        // Clear error messages on input
        const inputs = document.querySelectorAll('.auth-input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const errorDiv = this.parentElement.querySelector('.input-error');
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
            });
        });

        // Auto-focus on first invalid input
        document.addEventListener('DOMContentLoaded', function() {
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        });
    </script>
</body>
</html>
