window.__dashboardConfig = window.__dashboardConfig || {
    pusherKey: '',
    pusherCluster: 'mt1',
    userId: null,
    user: { name: '', firstName: '', avatar: '', type: '', verified: false, id: null }
};

['toggleSidebar', 'deleteTrip', 'loadUpcomingTrips', 'loadUserStatistics', 'logout']
    .forEach(function (fn) {
        if (!window[fn]) {
            window[fn] = function () {
                console.warn('[dashboard] ' + fn + ' called before module loaded');
            };
        }
    });

(function () {
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.content;
        var match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    var BUDGET_LABELS = {
        backpacker: 'Backpacker',
        budget: 'Budget',
        mid: 'Mid Range',
        premium: 'Premium',
        luxury: 'Luxury'
    };
    var DURATION_LABELS = {
        weekend: 'Long Weekend',
        week: 'One Week',
        two_weeks: 'Two Weeks',
        month: 'One Month+',
        flexible: 'Flexible'
    };
    var MOOD_ICONS_DASH = {
        adventurous: 'fa-hiking',
        relaxed: 'fa-spa',
        cultural: 'fa-landmark',
        romantic: 'fa-heart',
        foodie: 'fa-utensils',
        'eco-travel': 'fa-leaf'
    };

    function init() {
        initializeUserData();
        loadUpcomingTrips();
        loadRecentActivity();
        loadUserStatistics();
        initMenuItems();
        consumePendingTripSave();
        initTripSavedListener();

        if (typeof window.Currency !== 'undefined') {
            window.Currency.onCurrencyChange(function () {
                loadUpcomingTrips();
                if (typeof window.Currency.refreshAllPrices === 'function') {
                    window.Currency.refreshAllPrices();
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        loadUpcomingTrips();
        loadUserStatistics();
        consumePendingTripSave();
    });

    function consumePendingTripSave() {
        try {
            var raw = localStorage.getItem('smartBookingTripSaved');
            if (!raw) return;
            var payload = JSON.parse(raw);
            if (!payload.ts || Date.now() - payload.ts > 15000) {
                localStorage.removeItem('smartBookingTripSaved');
                return;
            }
            localStorage.removeItem('smartBookingTripSaved');
            loadUpcomingTrips();
            loadUserStatistics();
        } catch (_) {}
    }

    function initTripSavedListener() {
        window.addEventListener('storage', function (e) {
            if (e.key !== 'smartBookingTripSaved' || !e.newValue) return;
            try {
                JSON.parse(e.newValue);
                localStorage.removeItem('smartBookingTripSaved');
                loadUpcomingTrips();
                loadUserStatistics();
            } catch (_) {}
        });
    }

    function initMenuItems() {
        document.querySelectorAll('.menu-item').forEach(function (item) {
            item.addEventListener('click', function () {
                document.querySelectorAll('.menu-item').forEach(function (i) {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    }

    function initializeUserData() {
        var cfg = (window.__dashboardConfig && window.__dashboardConfig.user) || {};
        var welcomeMsg = document.getElementById('welcomeMessage');
        if (welcomeMsg) {
            var name = cfg.firstName || cfg.name || 'User';
            var h = new Date().getHours();
            var greeting = h < 12 ? 'Good morning' : h < 17 ? 'Good afternoon' : 'Good evening';
            welcomeMsg.textContent = greeting + ', ' + name + '!';
        }
    }

    function loadUpcomingTrips() {
        fetch('/api/trips/upcoming', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                renderTrips(data.trips || []);
            })
            .catch(function () {
                renderTrips([]);
            });
    }

    function loadRecentActivity() {
        var section = document.getElementById('recentActivityContent');
        if (!section) return;

        fetch('/api/user/recent-activity', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                renderRecentActivity(data.activities || []);
            })
            .catch(function () {
                section.innerHTML =
                    '<div class="empty-state"><i class="fas fa-clock"></i><h3>No Activity Yet</h3><p>Your recent actions will appear here.</p></div>';
            });
    }

    function renderRecentActivity(activities) {
        var section = document.getElementById('recentActivityContent');
        if (!section) return;

        if (!activities.length) {
            section.innerHTML =
                '<div class="empty-state">' +
                '<i class="fas fa-clock"></i>' +
                '<h3>No Activity Yet</h3>' +
                '<p>Plan a trip, search stays, or make a booking to see activity here.</p>' +
                '</div>';
            return;
        }

        section.innerHTML = activities
            .map(function (a) {
                return (
                    '<div class="activity-item clickable" data-action="navigate" data-url="' +
                    (a.url || '#') +
                    '">' +
                    '<div class="activity-icon" data-activity-color="' +
                    escapeHtml(a.color || '#c9a96e') +
                    '">' +
                    '<i class="fas ' +
                    (a.icon || 'fa-circle') +
                    '"></i>' +
                    '</div>' +
                    '<div class="activity-body">' +
                    '<div class="activity-title">' +
                    escapeHtml(a.title) +
                    '</div>' +
                    (a.sub ? '<div class="activity-sub">' + escapeHtml(a.sub) + '</div>' : '') +
                    '</div>' +
                    '<div class="activity-time">' +
                    escapeHtml(a.time) +
                    '</div>' +
                    '</div>'
                );
            })
            .join('');

        section.querySelectorAll('.activity-icon[data-activity-color]').forEach(function(icon) {
            const color = icon.dataset.activityColor || '#c9a96e';
            icon.style.background = color + '22';
            icon.style.color = color;
        });
    }

    function renderTrips(trips) {
        var section = document.getElementById('upcomingTripsContent');
        if (!section) return;

        if (!trips.length) {
            section.innerHTML =
                '<div class="empty-state">' +
                '<i class="fas fa-route"></i>' +
                '<h3>No Trips Planned Yet</h3>' +
                '<p>Start planning your next adventure!</p>' +
                '<button class="btn" data-action="navigate" data-url="/plan-trip">' +
                '<i class="fas fa-plus"></i> Create Your First Trip' +
                '</button>' +
                '</div>';
            return;
        }

        section.innerHTML = trips
            .map(function (t) {
                var icon = MOOD_ICONS_DASH[t.mood] || 'fa-globe';
                var budget = BUDGET_LABELS[t.budget] || t.budget || '—';
                var dur = DURATION_LABELS[t.duration] || t.duration || '—';
                var cost = t.estimated_cost
                    ? typeof window.Currency !== 'undefined'
                        ? window.Currency.format(Number(t.estimated_cost))
                        : '$' + Number(t.estimated_cost).toLocaleString()
                    : '—';
                return (
                    '<div class="trip-card">' +
                    '<div class="trip-card-header">' +
                    '<div class="trip-icon"><i class="fas ' +
                    icon +
                    '"></i></div>' +
                    '<div class="trip-info">' +
                    '<h4>' +
                    escapeHtml(t.destination) +
                    (t.country ? ', ' + escapeHtml(t.country) : '') +
                    '</h4>' +
                    '<p>' +
                    dur +
                    ' &nbsp;·&nbsp; ' +
                    budget +
                    '</p>' +
                    '</div>' +
                    '<div class="trip-cost">' +
                    cost +
                    '</div>' +
                    '</div>' +
                    '<div class="trip-meta">' +
                    (t.companion
                        ? '<span><i class="fas fa-users"></i> ' + String(t.companion).replace(/_/g, ' ') + '</span>'
                        : '') +
                    (t.month ? '<span><i class="fas fa-calendar"></i> ' + escapeHtml(t.month) + '</span>' : '') +
                    (t.origin
                        ? '<span><i class="fas fa-plane-departure"></i> from ' + escapeHtml(t.origin) + '</span>'
                        : '') +
                    '</div>' +
                    (t.feeling_note
                        ? '<div class="trip-feeling"><i class="fas fa-heart"></i> ' + escapeHtml(t.feeling_note) + '</div>'
                        : '') +
                    '<button class="trip-delete-btn" data-action="deleteTrip" data-params=\'{"args":[' +
                    t.id +
                    ']}\'>' +
                    '<i class="fas fa-trash-alt"></i>' +
                    '</button>' +
                    '</div>'
                );
            })
            .join('');
    }

    function deleteTrip(id) {
        Swal.fire({
            title: 'Remove Trip?',
            text: 'This will remove the trip from your dashboard.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f44336',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, remove it'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            fetch('/api/trips/' + id, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                }
            })
                .then(function () {
                    loadUpcomingTrips();
                    loadUserStatistics();
                    loadRecentActivity();
                })
                .catch(function () {});
        });
    }

    function loadUserStatistics() {
        fetch('/api/user/statistics', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                updateCounts(data);
            })
            .catch(function (e) {
                console.error('[stats]', e);
            });
    }

    function updateCounts(data) {
        data = data || {};

        function set(id, v) {
            var el = document.getElementById(id);
            if (!el) return;
            var current = parseInt(el.textContent, 10);
            if (v === 0 && current > 0) return;
            el.textContent = v;
        }

        function setSub(id, v) {
            var el = document.getElementById(id);
            if (el) el.textContent = v;
        }

        if (data.trips !== undefined) {
            set('statTripsCount', data.trips);
            setSub(
                'tripsSubtext',
                data.trips > 0 ? data.trips + ' trip' + (data.trips !== 1 ? 's' : '') + ' planned' : 'No trips yet'
            );
        }
        if (data.bookings !== undefined) {
            set('statBookingsCount', data.bookings);
            setSub(
                'bookingsSubtext',
                data.bookings > 0
                    ? data.bookings + ' booking' + (data.bookings !== 1 ? 's' : '') + ' active'
                    : 'No bookings yet'
            );
            set('bookingsCount', data.bookings);
            var bc = document.getElementById('bookingsCount');
            if (bc) bc.style.display = data.bookings > 0 ? '' : 'none';
        }
        if (data.stay_searches !== undefined) {
            set('statStaySearchesCount', data.stay_searches);
            setSub(
                'staySearchesSubtext',
                data.stay_searches > 0
                    ? data.stay_searches + ' search' + (data.stay_searches !== 1 ? 'es' : '')
                    : 'Browse accommodations'
            );
        }
    }

    function toggleSidebar() {
        var el = document.getElementById('sidebar');
        if (el) el.classList.toggle('active');
    }

    function logout() {
        Swal.fire({
            title: 'Logout',
            text: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#f44336',
            confirmButtonText: 'Yes, logout'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = csrfToken();
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        });
    }

    window.toggleSidebar = toggleSidebar;
    window.deleteTrip = deleteTrip;
    window.loadUpcomingTrips = loadUpcomingTrips;
    window.loadUserStatistics = loadUserStatistics;
    window.logout = logout;
    
    // Dashboard search handler
    window.dashboardSearch = function() {
        var input = document.getElementById('dashSearchInput');
        if (input && input.value.trim()) {
            window.location.href = '/flights?q=' + encodeURIComponent(input.value.trim());
        }
    };
})();


window.previewAvatar = function(inputEl) {
    var input = (inputEl && inputEl.files) ? inputEl : document.querySelector('input[name="profile_picture"]');
    if (!input || !input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('avatarPreview');
        var initial = document.getElementById('avatarInitial');
        if (!preview) {
            preview = document.createElement('img');
            preview.id = 'avatarPreview';
            preview.className = 'avatar-preview-img';
            if (initial) initial.replaceWith(preview);
        }
        preview.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
};

(function initDeleteModal() {
    var openBtn   = document.getElementById('openDeleteModal');
    var modal     = document.getElementById('deleteAccountModal');
    var closeBtn  = document.getElementById('closeDeleteModal');
    var cancelBtn = document.getElementById('cancelDeleteModal');

    function openModal()  { if (modal) modal.classList.add('open'); }
    function closeModal() { if (modal) modal.classList.remove('open'); }

    if (openBtn)   openBtn.addEventListener('click', openModal);
    if (closeBtn)  closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (modal)     modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('open')) closeModal();
    });
})();
