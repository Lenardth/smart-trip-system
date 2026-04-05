<x-guest-layout>
    <h2 class="auth-title">Create Account</h2>

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <div class="input-group">
            <label for="name"><i class="fas fa-user"></i> Full Name</label>
            <input id="name" class="auth-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   type="text" name="name" value="{{ old('name') }}"
                   required autofocus autocomplete="name" placeholder="Your full name">
            @error('name')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input id="email" class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   type="email" name="email" value="{{ old('email') }}"
                   required autocomplete="username" placeholder="you@example.com">
            @error('email')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label><i class="fas fa-id-badge"></i> Account Type</label>
            <div class="user-type-selector">
                <label class="type-option">
                    <input type="radio" name="user_type" value="user" id="type_traveler"
                           {{ old('user_type', 'user') === 'user' ? 'checked' : '' }}
                           onchange="toggleAgencyFields()">
                    <span class="type-card">
                        <span class="type-icon"><i class="fas fa-suitcase"></i></span>
                        <h4>Traveler</h4>
                        <p>Plan & book trips</p>
                    </span>
                </label>
                <label class="type-option">
                    <input type="radio" name="user_type" value="agency" id="type_agency"
                           {{ old('user_type') === 'agency' ? 'checked' : '' }}
                           onchange="toggleAgencyFields()">
                    <span class="type-card">
                        <span class="type-icon"><i class="fas fa-building"></i></span>
                        <h4>Agency</h4>
                        <p>Manage travel services</p>
                    </span>
                </label>
            </div>
        </div>

        <div class="agency-fields input-group" id="agency-fields">
            <label for="agency_name"><i class="fas fa-building"></i> Agency Name</label>
            <input id="agency_name" class="auth-input" type="text" name="agency_name"
                   value="{{ old('agency_name') }}" placeholder="Your agency name">
        </div>

        <div class="input-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input id="password" class="auth-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   type="password" name="password"
                   required autocomplete="new-password" placeholder="Min. 8 characters">
            @error('password')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="password_confirmation"><i class="fas fa-lock"></i> Confirm Password</label>
            <input id="password_confirmation" class="auth-input"
                   type="password" name="password_confirmation"
                   required autocomplete="new-password" placeholder="Repeat password">
        </div>

        @if ($errors->any())
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="auth-btn">
            <i class="fas fa-user-plus"></i> Create Account
        </button>

        <div class="auth-divider"><span>Already have an account?</span></div>

        <a href="{{ route('login') }}" class="auth-link" style="text-align:center;display:block;">
            Sign in instead
        </a>
    </form>
</x-guest-layout>
