// Global App Configuration
window.App = {
    userId: null,
    userName: '',
    userAvatar: '',
    userType: '',
    userVerified: false,
    pusherKey: '',
    pusherCluster: '',
    csrfToken: '',

    init() {
        const body = document.body;
        this.userId = body.dataset.userId || null;
        this.userName = body.dataset.userName || '';
        this.userAvatar = body.dataset.userAvatar || '';
        this.userType = body.dataset.userType || '';
        this.userVerified = body.dataset.userVerified === '1';
        this.pusherKey = body.dataset.pusherKey || '';
        this.pusherCluster = body.dataset.pusherCluster || '';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        this.initMobileMenu();
        this.initNotifications();

        console.log('Smart Booking App Initialized');
    },

    initMobileMenu() {
        const toggleBtn = document.querySelector('.mobile-toggle');
        const sidebar = document.getElementById('sidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.onclick = (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('mobile-open');
            };

            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 &&
                    sidebar.classList.contains('mobile-open') &&
                    !sidebar.contains(e.target) &&
                    !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            });
        }
    },

    initNotifications() {
        const notifBtn = document.querySelector('.notification-btn');
        const dropdown = document.getElementById('notificationDropdown');

        if (notifBtn && dropdown) {
            notifBtn.onclick = (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('show');
                if (dropdown.classList.contains('show')) {
                    this.loadNotifications();
                }
            };

            document.addEventListener('click', (e) => {
                if (!notifBtn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        }
    },

    async loadNotifications(tab = 'all') {
        const listContainer = document.getElementById('notificationList');
        if (!listContainer) return;

        try {
            const response = await fetch(`/api/notifications?tab=${tab}`);
            const data = await response.json();

            if (data.notifications && data.notifications.length > 0) {
                listContainer.innerHTML = data.notifications.map(notif => `
                    <div class="notification-item ${notif.read ? '' : 'unread'}" onclick="handleNotificationClick(${notif.id})">
                        <div class="notification-icon ${notif.type}">
                            <i class="fas ${this.getNotificationIcon(notif.type)}"></i>
                        </div>
                        <div class="notification-content">
                            <p>${notif.message}</p>
                            <span class="notification-time">${this.formatDate(notif.created_at)}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>No notifications</p>
                    </div>
                `;
            }

            const badge = document.getElementById('notificationCount');
            if (badge && data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = 'block';
            } else if (badge) {
                badge.style.display = 'none';
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },

    getNotificationIcon(type) {
        const icons = {
            chat: 'fa-comment',
            activity: 'fa-bell',
            booking: 'fa-ticket-alt',
            system: 'fa-cog'
        };
        return icons[type] || 'fa-bell';
    },

    formatDate(date, format = 'relative') {
        const d = new Date(date);
        const now = new Date();
        const diff = now - d;

        if (format === 'relative') {
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (days > 7) return d.toLocaleDateString();
            if (days > 0) return `${days}d ago`;
            if (hours > 0) return `${hours}h ago`;
            if (minutes > 0) return `${minutes}m ago`;
            return 'Just now';
        }

        return d.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    showToast(message, type = 'info') {
        const existingToast = document.querySelector('.toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${this.getToastIcon(type)}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    getToastIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }
};

// Global Functions
window.toggleSidebar = () => {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('mobile-open');
};

window.viewProfile = () => {
    window.location.href = '/profile';
};

window.openSettings = () => {
    window.location.href = '/settings';
};

window.openGallery = () => {
    const modal = document.getElementById('galleryModal');
    if (modal) modal.style.display = 'flex';
};

window.closeGallery = () => {
    const modal = document.getElementById('galleryModal');
    if (modal) modal.style.display = 'none';
};

window.uploadPhotos = () => {
    window.openGallery();
    setTimeout(() => {
        const fileInput = document.getElementById('mediaInput');
        if (fileInput) fileInput.click();
    }, 100);
};

window.toggleNotifications = () => {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) dropdown.classList.toggle('show');
    if (dropdown?.classList.contains('show')) {
        App.loadNotifications();
    }
};

window.switchNotificationTab = async (tab) => {
    document.querySelectorAll('.notification-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.notification-tab[data-tab="${tab}"]`).classList.add('active');
    await App.loadNotifications(tab);
};

window.markAllRead = async () => {
    try {
        const response = await fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        if (response.ok) {
            App.showToast('All notifications marked as read', 'success');
            await App.loadNotifications();
        }
    } catch (error) {
        console.error('Error marking all read:', error);
    }
};

window.openComposeMessage = () => {
    window.location.href = '/chat?compose=true';
};

window.handleNotificationClick = (id) => {
    fetch(`/api/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Content-Type': 'application/json'
        }
    });
};

window.autoResize = (textarea) => {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 150) + 'px';
};

window.togglePublicNav = () => {
    const nav = document.getElementById('publicNav');
    if (nav) nav.classList.toggle('open');
};

window.logout = () => {
    const form = document.querySelector('.logout-form');
    if (form) {
        form.submit();
    } else {
        const logoutForm = document.createElement('form');
        logoutForm.method = 'POST';
        logoutForm.action = '/logout';
        logoutForm.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
        `;
        document.body.appendChild(logoutForm);
        logoutForm.submit();
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    App.init();
}
