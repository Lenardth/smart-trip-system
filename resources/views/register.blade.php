<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register — Smart Booking</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Georgia', serif;
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
            position: relative;
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            margin-bottom: 6px;
            color: #3b1f2b;
            font-size: 14px;
            font-weight: 600;
        }
        .input-error {
            color: #721c24;
            font-size: 13px;
            margin-top: 5px;
        }
        .auth-input.is-invalid {
            border-color: #f5c6cb;
            background: #fff5f5;
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
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            color: #6b5b4f;
        }
        .user-type-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .user-type-card {
            flex: 1;
            padding: 20px;
            border: 2px solid #e2d5c7;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            background: #fff8f2;
        }
        .user-type-card:hover {
            border-color: #c9a96e;
            transform: translateY(-2px);
        }
        .user-type-card.selected {
            border-color: #3b1f2b;
            background: #f5f0eb;
        }
        .user-type-card .type-icon {
            font-size: 32px;
            color: #3b1f2b;
            margin-bottom: 10px;
        }
        .user-type-card h4 {
            margin: 0 0 8px 0;
            color: #3b1f2b;
            font-weight: normal;
        }
        .user-type-card p {
            margin: 0;
            font-size: 13px;
            color: #6b5b4f;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking">
            </div>
            <h1 class="auth-title">Create Account</h1>

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="auth-input @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        placeholder="John Doe"
                    >
                    @error('name')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="auth-input @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        placeholder="your@email.com"
                    >
                    @error('email')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group">
                    <label>Account Type</label>
                    <div class="user-type-selector">
                        <div class="user-type-card" onclick="selectUserType('user')" id="userTypeUser">
                            <div class="type-icon"><i class="fas fa-user"></i></div>
                            <h4>Traveler</h4>
                            <p>Plan trips, book flights, share memories</p>
                        </div>
                        <div class="user-type-card" onclick="selectUserType('agency')" id="userTypeAgency">
                            <div class="type-icon"><i class="fas fa-briefcase"></i></div>
                            <h4>Travel Agency</h4>
                            <p>List flights, manage bookings, grow business</p>
                        </div>
                    </div>
                    <input type="hidden" name="user_type" id="userTypeInput" value="user" required>
                    @error('user_type')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Agency-specific fields (hidden by default) -->
                <div class="input-group" id="agencyFields" style="display: none;">
                    <label for="agency_name">Agency Name</label>
                    <input
                        type="text"
                        id="agency_name"
                        name="agency_name"
                        class="auth-input"
                        placeholder="Your agency/business name"
                    >
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        required
                        placeholder="Minimum 8 characters"
                    >
                    <div class="password-strength">
                        <i class="fas fa-info-circle"></i> Must be at least 8 characters
                    </div>
                    @error('password')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="auth-input"
                        required
                        placeholder="Re-enter your password"
                    >
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-divider">
                <span>Already have an account?</span>
            </div>

            <a href="{{ route('login') }}" class="auth-link">
                <i class="fas fa-sign-in-alt"></i> Log In
            </a>

            <a href="/" class="auth-link" style="margin-top:10px;">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        function selectUserType(type) {
            document.getElementById('userTypeInput').value = type;

            // Update visual selection
            document.getElementById('userTypeUser').classList.remove('selected');
            document.getElementById('userTypeAgency').classList.remove('selected');

            if (type === 'user') {
                document.getElementById('userTypeUser').classList.add('selected');
                document.getElementById('agencyFields').style.display = 'none';
            } else {
                document.getElementById('userTypeAgency').classList.add('selected');
                document.getElementById('agencyFields').style.display = 'block';
            }
        }

        // Initialize with user type selected
        document.addEventListener('DOMContentLoaded', function() {
            selectUserType('user');
        });
    </script>
</body>
</html>
