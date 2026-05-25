<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="auth-title">Welcome Back</h2>

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="input-group">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input id="email" class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username" placeholder="you@example.com">
            @error('email')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <div class="password-wrapper">
                <input id="password" class="auth-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       type="password" name="password"
                       required autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password" aria-pressed="false">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="remember-me">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">Remember me</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="auth-btn" id="loginBtn">
            <i class="fas fa-sign-in-alt"></i> Log In
        </button>

        <div class="auth-divider"><span>Don't have an account?</span></div>

        <a href="{{ route('register') }}" class="auth-link">
            Create a free account
        </a>
    </form>
</x-guest-layout>
