document.addEventListener('DOMContentLoaded', function () {

    const toggleBtn  = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');
    const passwordInput = document.getElementById('password');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye', !isPassword);
            toggleIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    }

    const loginForm = document.getElementById('loginForm');
    const loginBtn  = document.getElementById('loginBtn');

    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function () {
            loginBtn.classList.add('loading');
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        });
    }

    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            this.classList.remove('is-invalid');
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            this.classList.remove('is-invalid');
        });
    }

});