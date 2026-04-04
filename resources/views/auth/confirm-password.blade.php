<x-guest-layout>
    <h2 class="auth-title">Confirm Password</h2>
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;text-align:center;">
        Please confirm your password before continuing.
    </p>
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="input-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password">
            @error('password') <div class="input-error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="auth-btn"><i class="fas fa-check"></i> Confirm</button>
    </form>
</x-guest-layout>
