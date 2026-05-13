document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('togglePassword');
    var input = document.getElementById('password');
    var icon = document.getElementById('toggleIcon');
    if (!btn || !input) return;

    btn.addEventListener('click', function () {
        var isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        if (icon) {
            icon.className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye';
        }
    });
});
