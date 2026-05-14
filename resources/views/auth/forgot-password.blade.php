<x-guest-layout>
    <h2 class="auth-title">Reset Password</h2>
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;text-align:center;">
        Enter your email and we'll send you a reset link.
    </p>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="input-group">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com">
            @error('email') <div class="input-error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="auth-btn"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
        <a href="{{ route('login') }}" class="auth-link">Back to Login</a>
    </form>
</x-guest-layout>