window.App = {
    init() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    },
    showToast(message, type = 'info') {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        document.querySelector('.app-toast')?.remove();
        const toast = document.createElement('div');
        toast.className = `app-toast app-toast--${type}`;
        toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
};

window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('active');
};

window.viewProfile = function() {
    window.location.href = '/dashboard';
};

window.openSettings = function() {
    window.location.href = '/dashboard';
};

window.togglePublicNav = function() {
    document.getElementById('publicNav')?.classList.toggle('open');
};

window.logout = function() {
    const existing = document.querySelector('.logout-form');
    if (existing) {
        existing.submit();
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">`;
    document.body.appendChild(form);
    form.submit();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    App.init();
}
