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
        body { margin: 0; padding: 0; font-family: 'Georgia', serif; }
        .error-message { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-size: 14px; }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 6px; color: #3b1f2b; font-size: 14px; font-weight: 600; }
        .input-error { color: #721c24; font-size: 13px; margin-top: 5px; }
        .auth-input.is-invalid { border-color: #f5c6cb; background: #fff5f5; }
        .auth-divider { text-align: center; margin: 25px 0; position: relative; }
        .auth-divider::before { content: ''; position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: #d4c4b0; }
        .auth-divider span { background: #fff8f2; padding: 0 15px; position: relative; color: #6b5b4f; font-size: 14px; }

        .user-type-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .type-option { position: relative; cursor: pointer; }
        .type-option input[type="radio"] { position: absolute; opacity: 0; }
        .type-card { background: #fff8f2; border: 2px solid #d4c4b0; border-radius: 8px; padding: 20px; text-align: center; transition: all 0.3s; cursor: pointer; }
        .type-option input[type="radio"]:checked + .type-card { border-color: #c9a96e; background: linear-gradient(135deg, #fff8f2, #fdf0dc); box-shadow: 0 4px 12px rgba(201,169,110,0.25); }
        .type-icon { font-size: 2.5em; color: #3b1f2b; margin-bottom: 10px; }
        .type-card h4 { color: #3b1f2b; margin: 0 0 5px; font-size: 16px; font-weight: normal; }
        .type-card p { color: #6b5b4f; margin: 0; font-size: 12px; }
        .agency-fields { display: none; }
        .agency-fields.show { display: block; }
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
                    <label>I am registering as:</label>
                    <div class="user-type-selector">
                        <div class="type-option">
                            <input type="radio" name="user_type" value="user" id="type_user" {{ old('user_type', 'user') == 'user' ? 'checked' : '' }} onchange="toggleAgencyFields()">
                            <label for="type_user" class="type-card">
                                <div class="type-icon"><i class="fas fa-user"></i></div>
                                <h4>Normal User</h4>
                                <p>Book trips & explore</p>
                            </label>
                        </div>
                        <div class="type-option">
                            <input type="radio" name="user_type" value="agency" id="type_agency" {{ old('user_type') == 'agency' ? 'checked' : '' }} onchange="toggleAgencyFields()">
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
                    <input type="text" id="name" name="name" class="auth-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus placeholder="John Doe">
                    @error('name')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="agency-fields {{ old('user_type') == 'agency' ? 'show' : '' }}" id="agency-fields">
                    <div class="input-group">
                        <label for="agency_name">Agency Name</label>
                        <input type="text" id="agency_name" name="agency_name" class="auth-input @error('agency_name') is-invalid @enderror" value="{{ old('agency_name') }}" placeholder="Travel Co. Ltd.">
                        @error('agency_name')
                            <div class="input-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="auth-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="your@email.com">
                    @error('email')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="auth-input @error('password') is-invalid @enderror" required placeholder="Minimum 8 characters">
                    @error('password')
                        <div class="input-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="auth-input" required placeholder="Re-enter your password">
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

    <script>
        function toggleAgencyFields() {
            const agencyFields = document.getElementById('agency-fields');
            const isAgency = document.getElementById('type_agency').checked;
            const agencyNameInput = document.getElementById('agency_name');

            if (isAgency) {
                agencyFields.classList.add('show');
                // Remove the required attribute - let Laravel handle validation
                // agencyNameInput.removeAttribute('required');
            } else {
                agencyFields.classList.remove('show');
                // Clear the agency_name field when not needed
                agencyNameInput.value = '';
                // agencyNameInput.removeAttribute('required');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleAgencyFields();

            // Form validation before submission
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const isAgency = document.getElementById('type_agency').checked;
                const agencyNameInput = document.getElementById('agency_name');

                if (isAgency && !agencyNameInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter your agency name.');
                    agencyNameInput.focus();
                    return false;
                }

                return true;
            });
        });
    </script>
</body>
</html>
