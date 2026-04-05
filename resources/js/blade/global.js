window.App = {
    init() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    },
    showToast(message, type = 'info') {
        const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        document.querySelector('.app-toast')?.remove();
        const toast = document.createElement('div');
        toast.className = 'app-toast';
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#3b1f2b;color:#fff;padding:12px 20px;border-radius:8px;font-size:14px;display:flex;align-items:center;gap:10px;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.25);';
        toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
};

window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('active');
};

window.viewProfile = function () {
    window.location.href = '/profile';
};

window.openSettings = function () {
    window.location.href = '/settings';
};

window.togglePublicNav = function () {
    document.getElementById('publicNav')?.classList.toggle('open');
};

window.logout = function () {
    const existing = document.querySelector('.logout-form');
    if (existing) { existing.submit(); return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">`;
    document.body.appendChild(form);
    form.submit();
};

window.toggleNotifications = function () {
    document.getElementById('notificationDropdown')?.classList.toggle('active');
};

window.switchNotificationTab = function (tab) {
    document.querySelectorAll('.notification-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.notification-tab[data-tab="${tab}"]`)?.classList.add('active');
};

window.markAllRead = function () {
    fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
    }).then(() => {
        document.querySelectorAll('.notification-item.unread').forEach(el => el.classList.remove('unread'));
        const badge = document.getElementById('notificationCount');
        if (badge) badge.style.display = 'none';
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'All marked as read', icon: 'success', timer: 1500, showConfirmButton: false, confirmButtonColor: '#c9a96e' });
        }
    }).catch(console.error);
};

window.openComposeMessage = function () {
    window.location.href = '/chat';
};

window.handleNotificationClick = function (id) {
    fetch(`/api/notifications/${id}/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Content-Type': 'application/json' }
    }).catch(() => {});
};

window.openGallery = window.openGallery || function () {
    const modal = document.getElementById('galleryModal');
    if (modal) { modal.style.display = 'flex'; modal.classList.add('active'); }
};

window.closeGallery = window.closeGallery || function () {
    const modal = document.getElementById('galleryModal');
    if (modal) { modal.style.display = 'none'; modal.classList.remove('active'); }
};

window.uploadPhotos = window.uploadPhotos || function () {
    window.openGallery();
    setTimeout(() => document.getElementById('mediaInput')?.click(), 100);
};

document.addEventListener('click', function (e) {
    const dropdown = document.getElementById('notificationDropdown');
    const btn = document.querySelector('.notification-btn');
    if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.remove('active');
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    App.init();
}
