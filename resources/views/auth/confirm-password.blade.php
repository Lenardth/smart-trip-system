<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirm Password — Smart Booking</title>
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

        .info-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-box i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }
    </style>
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
