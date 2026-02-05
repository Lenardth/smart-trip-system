<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — Smart Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-page {
            width: 100%;
            max-width: 450px;
        }

        .auth-card {
            background: #fff8f2;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .auth-logo img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .auth-title {
            text-align: center;
            color: #3b1f2b;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .auth-subtitle {
            text-align: center;
            color: #6b5b4f;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            font-size: 14px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #3b1f2b;
            font-size: 14px;
            font-weight: 600;
        }

        .auth-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #d4c4b0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Georgia', serif;
            transition: all 0.3s ease;
            background: white;
        }

        .auth-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .auth-input.is-invalid {
            border-color: #f5c6cb;
            background: #fff5f5;
        }

        .input-error {
            color: #721c24;
            font-size: 13px;
            margin-top: 5px;
        }

        .auth-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            font-family: 'Georgia', serif;
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .auth-btn:active {
            transform: translateY(0);
        }

        .auth-divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #d4c4b0;
        }

        .auth-divider span {
            background: #fff8f2;
            padding: 0 15px;
            position: relative;
            color: #6b5b4f;
            font-size: 14px;
        }

        .auth-link {
            display: block;
            text-align: center;
            padding: 12px;
            color: #667eea;
            text-decoration: none;
            border: 2px solid #667eea;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
            margin-top: 10px;
        }

        .auth-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
    </style>
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
