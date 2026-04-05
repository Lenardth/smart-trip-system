(function () {
    var allNotifications = [];
    var currentTab = 'all';

    var TYPE_ICONS = {
        chat:    'fas fa-comments',
        booking: 'fas fa-ticket-alt',
        trip:    'fas fa-route',
        photo:   'fas fa-images',
        system:  'fas fa-info-circle'
    };

    var TYPE_ROUTES = {
        chat:    '/chat',
        booking: '/bookings',
        trip:    '/plan-trip'
    };

    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }

    function loadNotifications() {
        fetch('/api/notifications')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                allNotifications = data.notifications || [];
                updateCounts();
                render();
            })
            .catch(function () {
                var list = document.getElementById('notifList');
                if (list) {
                    list.innerHTML =
                        '<div class="notif-empty">' +
                            '<i class="fas fa-exclamation-circle"></i>' +
                            '<h3>Could not load notifications</h3>' +
                            '<p>Please try refreshing the page.</p>' +
                        '</div>';
                }
            });
    }

    function updateCounts() {
        var chat     = allNotifications.filter(function (n) { return n.type === 'chat'; });
        var activity = allNotifications.filter(function (n) { return n.type !== 'chat'; });

        var countAll      = document.getElementById('countAll');
        var countChat     = document.getElementById('countChat');
        var countActivity = document.getElementById('countActivity');
        var subtitle      = document.getElementById('notifSubtitle');

        if (countAll)      countAll.textContent      = allNotifications.length || '';
        if (countChat)     countChat.textContent     = chat.length || '';
        if (countActivity) countActivity.textContent = activity.length || '';

        var unread = allNotifications.filter(function (n) { return !n.read; }).length;
        if (subtitle) {
            subtitle.textContent = unread > 0
                ? unread + ' unread notification' + (unread > 1 ? 's' : '')
                : "You're all caught up!";
        }
    }

    function switchTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.notif-tab').forEach(function (t) { t.classList.remove('active'); });
        var active = document.querySelector('[data-tab="' + tab + '"]');
        if (active) active.classList.add('active');
        render();
    }

    function render() {
        var list = document.getElementById('notifList');
        if (!list) return;

        var items = allNotifications;
        if (currentTab === 'chat')     items = allNotifications.filter(function (n) { return n.type === 'chat'; });
        if (currentTab === 'activity') items = allNotifications.filter(function (n) { return n.type !== 'chat'; });

        if (!items.length) {
            list.innerHTML =
                '<div class="notif-empty">' +
                    '<i class="fas fa-bell-slash"></i>' +
                    '<h3>No notifications</h3>' +
                    '<p>Nothing here yet — check back later.</p>' +
                '</div>';
            return;
        }

        list.innerHTML = items.map(function (n) {
            var iconContent;
            if (n.user && n.user.avatar) {
                iconContent = '<img src="' + n.user.avatar + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
            } else if (n.user && n.user.initials) {
                iconContent = '<span style="font-size:16px;font-weight:700;color:white;">' + n.user.initials + '</span>';
            } else {
                iconContent = '<i class="' + (TYPE_ICONS[n.type] || 'fas fa-bell') + '"></i>';
            }

            var iconBg = (n.user && !n.user.avatar)
                ? ' style="background:linear-gradient(135deg,var(--gold),var(--deep));"'
                : '';

            return '<div class="notif-item ' + (n.read ? '' : 'unread') + '" data-id="' + n.id + '">' +
                '<div class="notif-icon ' + (n.type || '') + '"' + iconBg + '>' + iconContent + '</div>' +
                '<div class="notif-body">' +
                    '<h4>' + (n.title || '') + '</h4>' +
                    '<p>' + (n.message || '') + '</p>' +
                    '<div class="notif-time"><i class="fas fa-clock"></i> ' + (n.time || '') + '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function handleItemClick(e) {
        var item = e.target.closest('.notif-item[data-id]');
        if (!item) return;

        var id    = item.getAttribute('data-id');
        var notif = allNotifications.find(function (n) { return String(n.id) === id; });
        if (!notif) return;

        if (!notif.read) {
            notif.read = true;
            item.classList.remove('unread');
            updateCounts();
            fetch('/api/notifications/mark-read', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body:    JSON.stringify({ ids: [id] }),
            }).catch(function () {});
        }

        var dest = notif.url || TYPE_ROUTES[notif.type];
        if (dest) {
            window.location.href = dest;
        } else if (notif.type === 'photo') {
            window.location.href = '/dashboard';
        }
    }

    function markAllRead() {
        allNotifications = allNotifications.map(function (n) { return Object.assign({}, n, { read: true }); });
        updateCounts();
        render();
        fetch('/api/notifications/mark-all-read', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        }).catch(function () {});
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'All marked as read', icon: 'success', timer: 1500, showConfirmButton: false, confirmButtonColor: '#c9a96e' });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadNotifications();

        var list = document.getElementById('notifList');
        if (list) list.addEventListener('click', handleItemClick);
    });

    window.switchTab   = switchTab;
    window.markAllRead = markAllRead;

    if (typeof window.viewProfile !== 'function') {
        window.viewProfile = function () { window.location.href = '/profile/edit'; };
    }

    if (typeof window.logout !== 'function') {
        window.logout = function () {
            fetch('/logout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            }).catch(function () {}).finally(function () {
                window.location.href = '/login';
            });
        };
    }
}());