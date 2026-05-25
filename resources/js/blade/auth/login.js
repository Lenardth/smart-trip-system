function initLoginPasswordToggle() {
    var btn = document.getElementById('togglePassword');
    var input = document.getElementById('password');
    var icon = document.getElementById('toggleIcon');
    if (!btn || !input) return;
    if (btn.dataset.passwordToggleReady === '1') return;
    btn.dataset.passwordToggleReady = '1';

    btn.addEventListener('click', function () {
        var isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        btn.setAttribute('aria-pressed', isPwd ? 'true' : 'false');
        btn.setAttribute('aria-label', isPwd ? 'Hide password' : 'Show password');
        if (icon) {
            icon.className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye';
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLoginPasswordToggle);
} else {
    initLoginPasswordToggle();
}
