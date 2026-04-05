<x-guest-layout>
    <h2 class="auth-title">Set New Password</h2>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="input-group">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username">
            @error('email') <div class="input-error">{{ $message }}</div> @enderror
        </div>
        <div class="input-group">
            <label for="password"><i class="fas fa-lock"></i> New Password</label>
            <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password">
            @error('password') <div class="input-error">{{ $message }}</div> @enderror
        </div>
        <div class="input-group">
            <label for="password_confirmation"><i class="fas fa-lock"></i> Confirm Password</label>
            <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>
        <button type="submit" class="auth-btn"><i class="fas fa-key"></i> Reset Password</button>
    </form>
</x-guest-layout>
