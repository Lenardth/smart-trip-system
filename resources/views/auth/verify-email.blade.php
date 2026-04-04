<x-guest-layout>
    <h2 class="auth-title">Verify Your Email</h2>
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;text-align:center;">
        Thanks for signing up! Please verify your email address by clicking the link we sent you.
    </p>
    @if(session('status') === 'verification-link-sent')
        <div class="profile-alert profile-alert--success" style="margin-bottom:16px;">
            <i class="fas fa-check-circle"></i> A new verification link has been sent.
        </div>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="auth-btn"><i class="fas fa-envelope"></i> Resend Verification Email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:12px;">
        @csrf
        <button type="submit" class="auth-btn" style="background:transparent;color:var(--text-muted);border:1px solid var(--border);">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </button>
    </form>
</x-guest-layout>
