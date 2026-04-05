window.__dashboardConfig = window.__dashboardConfig || {
    pusherKey: "",
    pusherCluster: "mt1",
    userId: null,
    user: {
        name: "",
        firstName: "",
        avatar: "",
        type: "",
        verified: false,
        id: null
    }
};

(function() {
    var mediaLibrary = [];
    var selectedMedia = new Set();
    var currentMediaIndex = 0;
    var notifications = [];
    var currentTab = 'all';
    var unreadCount = 0;
    var notifPollingInterval = null;

    document.addEventListener('DOMContentLoaded', function() {
        initializeUserData();
        loadMediaFromServer();
        loadNotifications();
        loadUpcomingTrips();
        loadRecentActivity();

        if (!notifPollingInterval) {
            notifPollingInterval = setInterval(loadNotifications, 5000);
        }

        initUploadArea();
        initMenuItems();
        initOutsideClickHandlers();
        initializeRealTimeChat();
        initNotificationListDelegate();
        initTripSavedListener();
        initWishlistUpdateListener();
        consumePendingTripSave();
    });
    window.addEventListener('pageshow', function(e) {
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
            if (!payload.ts || (Date.now() - payload.ts) > 15000) {
                localStorage.removeItem('smartBookingTripSaved');
                return;
            }
            localStorage.removeItem('smartBookingTripSaved');
            showTripSavedToast(payload.destination || 'Your trip');
        } catch (_) {}
    }

    function initTripSavedListener() {
        window.addEventListener('storage', function(e) {
            if (e.key !== 'smartBookingTripSaved' || !e.newValue) return;
            try {
                var payload = JSON.parse(e.newValue);
                localStorage.removeItem('smartBookingTripSaved');
                loadUpcomingTrips();
                loadUserStatistics();
                showTripSavedToast(payload.destination || 'Your trip');
            } catch (_) {}
        });
    }


    function initWishlistUpdateListener() {
        window.addEventListener('storage', function(e) {
            if (e.key !== 'smartBookingWishlistUpdated' || !e.newValue) return;

            try {
                localStorage.removeItem('smartBookingWishlistUpdated');
            } catch (_) {}

            loadUserStatistics();
        });
    }

    function showTripSavedToast(destination) {
        var toast = document.createElement('div');
        toast.className = 'chat-toast';
        toast.innerHTML =
            '<div class="chat-toast-inner">' +
            '<div class="chat-toast-avatar" style="background:linear-gradient(135deg,#43a047,#2e7d32);">' +
            '<i class="fas fa-route" style="font-size:18px;"></i>' +
            '</div>' +
            '<div class="chat-toast-body">' +
            '<div class="chat-toast-sender"><i class="fas fa-bookmark"></i> Trip Saved</div>' +
            '<div class="chat-toast-preview">' + destination + ' added to your dashboard</div>' +
            '</div>' +
            '<button class="chat-toast-close" onclick="this.closest(\'.chat-toast\').remove()">' +
            '<i class="fas fa-times"></i>' +
            '</button>' +
            '</div>';
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.style.animation = 'slideOutRight 0.4s ease forwards';
            setTimeout(function() {
                if (toast.parentNode) toast.remove();
            }, 400);
        }, 4000);
    }

    function initUploadArea() {
        var uploadArea = document.getElementById('uploadArea');
        if (!uploadArea) return;
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(ev) {
            uploadArea.addEventListener(ev, function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });
        ['dragenter', 'dragover'].forEach(function(ev) {
            uploadArea.addEventListener(ev, function() {
                uploadArea.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function(ev) {
            uploadArea.addEventListener(ev, function() {
                uploadArea.classList.remove('dragover');
            });
        });
        uploadArea.addEventListener('drop', function(e) {
            handleFileSelect({
                target: {
                    files: e.dataTransfer.files
                }
            });
        });
    }

    function initMenuItems() {
        document.querySelectorAll('.menu-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') e.preventDefault();
                document.querySelectorAll('.menu-item').forEach(function(i) {
                    i.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    }

    function initOutsideClickHandlers() {
        document.addEventListener('click', function(event) {
            var sidebar = document.getElementById('sidebar');
            var toggle = document.querySelector('.mobile-toggle');
            if (window.innerWidth <= 768 && sidebar && toggle) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
            var dropdown = document.getElementById('notificationDropdown');
            var bell = document.querySelector('.notification-btn');
            if (dropdown && bell &&
                !dropdown.contains(event.target) &&
                !bell.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
    }

    function initNotificationListDelegate() {
        var list = document.getElementById('notificationList');
        if (!list) return;
        list.addEventListener('click', function(e) {
            var item = e.target.closest('.notification-item[data-id]');
            if (!item) return;
            handleNotificationClick(item.getAttribute('data-id'));
        });
    }

    function initializeRealTimeChat() {
        var cfg = window.__dashboardConfig || {};
        if (!cfg.pusherKey || !cfg.userId) return;

        try {
            window.Pusher.logToConsole = false;
            window.Echo = new window.Echo({
                broadcaster: 'pusher',
                key: cfg.pusherKey,
                cluster: cfg.pusherCluster,
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken()
                    },
                },
            });

            window.Echo.private('chat.' + cfg.userId)
                .listen('.new-message', function(data) {
                    showChatToast(data.sender.name, data.body, data.sender_id);
                    loadNotifications(true);
                });
        } catch (err) {
            console.warn('[dashboard] Echo init failed:', err);
        }
    }

    function showChatToast(sender, message, senderId) {
        var toast = document.createElement('div');
        toast.className = 'chat-toast';
        var preview = message.length > 60 ? message.substring(0, 60) + '…' : message;
        var initials = sender.split(' ').map(function(n) {
            return n[0];
        }).join('').toUpperCase().substring(0, 2);

        toast.innerHTML =
            '<div class="chat-toast-inner">' +
            '<div class="chat-toast-avatar">' + initials + '</div>' +
            '<div class="chat-toast-body">' +
            '<div class="chat-toast-sender"><i class="fas fa-comments"></i> ' + sender + '</div>' +
            '<div class="chat-toast-preview">' + preview + '</div>' +
            '</div>' +
            '<button class="chat-toast-close" onclick="event.stopPropagation();this.closest(\'.chat-toast\').remove()">' +
            '<i class="fas fa-times"></i>' +
            '</button>' +
            '</div>';

        toast.onclick = function() {
            window.location.href = '/chat/' + (senderId || '');
            toast.remove();
        };

        document.body.appendChild(toast);
        playNotificationSound();

        setTimeout(function() {
            toast.style.animation = 'slideOutRight 0.4s ease forwards';
            setTimeout(function() {
                if (toast.parentNode) toast.remove();
            }, 400);
        }, 5000);
    }

    function playNotificationSound() {
        try {
            var ctx = new(window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.3);
        } catch (_) {}
    }

    function loadNotifications(silent) {
        fetch('/api/notifications', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                notifications = data.notifications || [];
                unreadCount = notifications.filter(function(n) {
                    return !n.read;
                }).length;
                updateNotificationBadge();
                renderNotifications();
            })
            .catch(function() {
                if (!silent) {
                    notifications = [];
                    updateNotificationBadge();
                    renderNotifications();
                }
            });
    }

    function toggleNotifications() {
        var dropdown = document.getElementById('notificationDropdown');
        if (!dropdown) return;
        dropdown.classList.toggle('active');
        if (dropdown.classList.contains('active')) {
            setTimeout(markVisibleAsRead, 1500);
        }
    }

    function switchNotificationTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.notification-tab').forEach(function(t) {
            t.classList.remove('active');
        });
        var el = document.querySelector('[data-tab="' + tab + '"]');
        if (el) el.classList.add('active');
        renderNotifications();
    }

    function renderNotifications() {
        var listEl = document.getElementById('notificationList');
        if (!listEl) return;

        var filtered = notifications;
        if (currentTab === 'chat') filtered = notifications.filter(function(n) {
            return n.type === 'chat';
        });
        if (currentTab === 'activity') filtered = notifications.filter(function(n) {
            return n.type !== 'chat';
        });

        if (!filtered.length) {
            listEl.innerHTML =
                '<div class="empty-notifications">' +
                '<i class="fas fa-bell-slash"></i>' +
                '<h4>No notifications</h4>' +
                '<p>You\'re all caught up!</p>' +
                '</div>';
            return;
        }

        listEl.innerHTML = filtered.map(function(notif) {
            var avatarHtml = notif.user ?
                (notif.user.avatar ?
                    '<img src="' + notif.user.avatar + '" style="width:45px;height:45px;border-radius:50%;object-fit:cover;">' :
                    '<div style="width:45px;height:45px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--deep));color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:16px;">' + notif.user.initials + '</div>') :
                '<i class="' + getNotificationIcon(notif.type) + '"></i>';

            return '<div class="notification-item ' + (notif.read ? '' : 'unread') + '" data-id="' + notif.id + '">' +
                '<div class="notification-icon-wrapper ' + notif.type + '">' + avatarHtml + '</div>' +
                '<div class="notification-content">' +
                '<h4>' + notif.title + '</h4>' +
                '<p>' + notif.message + '</p>' +
                '<div class="notification-time"><i class="fas fa-clock"></i> ' + notif.time + '</div>' +
                '</div>' +
                '</div>';
        }).join('');
    }

    function getNotificationIcon(type) {
        var map = {
            chat: 'fas fa-comments',
            booking: 'fas fa-ticket-alt',
            trip: 'fas fa-route',
            photo: 'fas fa-images',
            system: 'fas fa-info-circle'
        };
        return map[type] || 'fas fa-bell';
    }

    function updateNotificationBadge() {
        var badge = document.getElementById('notificationCount');
        if (!badge) return;
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    function markAllRead() {
        notifications = notifications.map(function(n) {
            return Object.assign({}, n, {
                read: true
            });
        });
        unreadCount = 0;
        updateNotificationBadge();
        renderNotifications();
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
        }).catch(console.error);
        Swal.fire({
            title: 'All marked as read',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    }

    function markVisibleAsRead() {
        var unread = notifications.filter(function(n) {
            return !n.read;
        });
        if (!unread.length) return;
        var ids = unread.map(function(n) {
            return n.id;
        });
        notifications = notifications.map(function(n) {
            return ids.indexOf(n.id) !== -1 ? Object.assign({}, n, {
                read: true
            }) : n;
        });
        unreadCount = notifications.filter(function(n) {
            return !n.read;
        }).length;
        updateNotificationBadge();
        renderNotifications();
        fetch('/api/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({
                ids: ids
            }),
        }).catch(console.error);
    }

    function handleNotificationClick(id) {
        var notif = notifications.find(function(n) {
            return n.id === id;
        });
        if (!notif) return;

        notifications = notifications.map(function(n) {
            return n.id === id ? Object.assign({}, n, {
                read: true
            }) : n;
        });
        unreadCount = notifications.filter(function(n) {
            return !n.read;
        }).length;
        updateNotificationBadge();
        renderNotifications();

        fetch('/api/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({
                ids: [id]
            }),
        }).catch(function() {});

        if (notif.url) {
            window.location.href = notif.url;
            return;
        }

        var routes = {
            chat: '/chat',
            booking: '/bookings',
            trip: '/plan-trip'
        };
        if (routes[notif.type]) {
            window.location.href = routes[notif.type];
        } else if (notif.type === 'photo') {
            openGallery();
            toggleNotifications();
        }
    }

    function openComposeMessage() {
        var dropdown = document.getElementById('notificationDropdown');
        if (dropdown) dropdown.classList.remove('active');
        window.location.href = '/chat';
    }

    function initializeUserData() {
        var cfg = (window.__dashboardConfig && window.__dashboardConfig.user) || {};
        var welcomeMsg = document.getElementById('welcomeMessage');
        if (welcomeMsg) {
            var name = cfg.firstName || cfg.name || 'User';
            welcomeMsg.textContent = 'Welcome Back, ' + name + '!';
        }
        loadUserStatistics();
    }

    function loadUpcomingTrips() {
        fetch('/api/trips/upcoming', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                renderTrips(data.trips || []);
            })
            .catch(function() {
                renderTrips([]);
            });
    }

    function loadRecentActivity() {
        var section = document.getElementById('recentActivityContent');
        if (!section) return;

        fetch('/api/user/recent-activity', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                renderRecentActivity(data.activities || []);
            })
            .catch(function() {
                if (section) section.innerHTML =
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
                '<p>Plan a trip, make a booking, or save a destination to see your activity here.</p>' +
                '</div>';
            return;
        }

        section.innerHTML = activities.map(function(a) {
            return '<div class="activity-item" onclick="window.location.href=\'' + (a.url || '#') + '\'" style="cursor:pointer;">' +
                '<div class="activity-icon" style="background:' + (a.color || 'var(--gold)') + '22;color:' + (a.color || 'var(--gold)') + ';">' +
                '<i class="fas ' + (a.icon || 'fa-circle') + '"></i>' +
                '</div>' +
                '<div class="activity-body">' +
                '<div class="activity-title">' + escapeHtml(a.title) + '</div>' +
                (a.sub ? '<div class="activity-sub">' + escapeHtml(a.sub) + '</div>' : '') +
                '</div>' +
                '<div class="activity-time">' + escapeHtml(a.time) + '</div>' +
                '</div>';
        }).join('');
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    var BUDGET_LABELS = {
        backpacker: 'Backpacker',
        budget: 'Budget',
        mid: 'Mid-Range',
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

    function renderTrips(trips) {
        var section = document.getElementById('upcomingTripsContent');
        var countEl = document.getElementById('statTripsCount');
        if (countEl) countEl.textContent = trips.length;
        if (!section) return;

        if (!trips.length) {
            section.innerHTML =
                '<div class="empty-state">' +
                '<i class="fas fa-route"></i>' +
                '<h3>No Trips Planned Yet</h3>' +
                '<p>Start planning your next adventure!</p>' +
                '<button class="btn" onclick="window.location.href=\'/plan-trip\'">' +
                '<i class="fas fa-plus"></i> Create Your First Trip' +
                '</button>' +
                '</div>';
            return;
        }

        section.innerHTML = trips.map(function(t) {
            var icon = MOOD_ICONS_DASH[t.mood] || 'fa-globe';
            var budget = BUDGET_LABELS[t.budget] || t.budget || '—';
            var dur = DURATION_LABELS[t.duration] || t.duration || '—';
            var cost = t.estimated_cost ? '$' + Number(t.estimated_cost).toLocaleString() : '—';
            return '<div class="trip-card">' +
                '<div class="trip-card-header">' +
                '<div class="trip-icon"><i class="fas ' + icon + '"></i></div>' +
                '<div class="trip-info">' +
                '<h4>' + t.destination + (t.country ? ', ' + t.country : '') + '</h4>' +
                '<p>' + dur + ' &nbsp;·&nbsp; ' + budget + '</p>' +
                '</div>' +
                '<div class="trip-cost">' + cost + '</div>' +
                '</div>' +
                '<div class="trip-meta">' +
                (t.companion ? '<span><i class="fas fa-users"></i> ' + t.companion.replace(/_/g, ' ') + '</span>' : '') +
                (t.month ? '<span><i class="fas fa-calendar"></i> ' + t.month + '</span>' : '') +
                (t.origin ? '<span><i class="fas fa-plane-departure"></i> from ' + t.origin + '</span>' : '') +
                '</div>' +
                (t.feeling_note ? '<div class="trip-feeling"><i class="fas fa-heart"></i> ' + t.feeling_note + '</div>' : '') +
                '<button class="trip-delete-btn" onclick="deleteTrip(' + t.id + ')">' +
                '<i class="fas fa-trash-alt"></i>' +
                '</button>' +
                '</div>';
        }).join('');
    }

    function deleteTrip(id) {
        Swal.fire({
            title: 'Remove Trip?',
            text: 'This will remove the trip from your dashboard.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f44336',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, remove it',
        }).then(function(result) {
            if (!result.isConfirmed) return;
            fetch('/api/trips/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
            }).then(function() {
                loadUpcomingTrips();
            }).catch(function() {});
        });
    }

    window.deleteTrip = deleteTrip;
    window.loadUpcomingTrips = loadUpcomingTrips;
    window.loadUserStatistics = loadUserStatistics;

    function loadUserStatistics() {
        fetch('/api/user/statistics', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                updateCounts(data);
            })
            .catch(function(e) {
                console.error('[stats]', e);
            });
    }

    function updateCounts(data) {
        data = data || {};
        var photos = data.photos !== undefined ? data.photos : mediaLibrary.length;
        var trips = data.trips !== undefined ? data.trips : 0;
        var bookings = data.bookings !== undefined ? data.bookings : 0;
        var saved = data.saved !== undefined ? data.saved : 0;
        var notifs = data.notifications !== undefined ? data.notifications : 0;

        function set(id, v) {
            var el = document.getElementById(id);
            if (el) el.textContent = v;
        }
        set('photosCount', photos);
        set('statPhotosCount', photos);
        set('bookingsCount', bookings);
        set('statBookingsCount', bookings);
        set('savedCount', saved);
        set('statSavedCount', saved);
        set('statTripsCount', trips);

        var badge = document.getElementById('notificationCount');
        if (badge) {
            badge.textContent = notifs;
            badge.style.display = notifs > 0 ? 'block' : 'none';
        }
    }

    function openGallery() {
        var el = document.getElementById('galleryModal');
        if (el) el.classList.add('active');
        renderGallery();
    }

    function closeGallery() {
        var el = document.getElementById('galleryModal');
        if (el) el.classList.remove('active');
    }

    function triggerFileInput() {
        var el = document.getElementById('mediaInput');
        if (el) el.click();
    }

    function handleFileSelect(event) {
        var files = Array.from((event && event.target && event.target.files) || []);
        if (!files.length) return;
        var fd = new FormData();
        files.forEach(function(file) {
            fd.append('media[]', file);
        });
        fetch('/api/media/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: fd
            })
            .then(function(r) {
                return r.json();
            })
            .then(function() {
                loadMediaFromServer();
                Swal.fire({
                    title: 'Uploaded',
                    text: files.length + ' file(s) uploaded successfully.',
                    icon: 'success',
                    timer: 1400,
                    showConfirmButton: false
                });
            })
            .catch(function() {
                Swal.fire({
                    title: 'Upload failed',
                    text: 'Please try again.',
                    icon: 'error'
                });
            });
        if (event.target && event.target.value !== undefined) {
            event.target.value = '';
        }
    }

    function renderGallery() {
        var grid = document.getElementById('galleryGrid');
        if (!grid) return;
        if (!mediaLibrary.length) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);">' +
                '<i class="fas fa-images" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px;"></i>' +
                '<p>No photos yet. Upload your first photo above.</p></div>';
            return;
        }
        grid.innerHTML = mediaLibrary.map(function(item, i) {
            return '<div class="gallery-item" style="position:relative;">' +
                (item.type === 'image' ?
                    '<img src="' + item.src + '" alt="' + item.name + '" onclick="viewMedia(' + i + ')" style="cursor:pointer;">' :
                    '<video src="' + item.src + '" onclick="viewMedia(' + i + ')" style="cursor:pointer;"></video>' +
                    '<div class="video-badge"><i class="fas fa-play"></i> Video</div>') +
                '<div class="gallery-item-actions">' +
                '<button onclick="editMediaTitle(' + i + ')" title="Edit title"><i class="fas fa-edit"></i></button>' +
                '<button onclick="deleteSingleMedia(' + i + ')" title="Delete" style="color:#f44336;"><i class="fas fa-trash"></i></button>' +
                '</div>' +
                '<div class="gallery-item-name">' + (item.name || '') + '</div>' +
                '</div>';
        }).join('');
    }

    function editMediaTitle(index) {
        var item = mediaLibrary[index];
        if (!item) return;
        if (typeof Swal === 'undefined') {
            var newTitle = prompt('Edit title:', item.name || '');
            if (newTitle === null) return;
            fetch('/api/media/' + item.id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({
                    title: newTitle
                })
            }).then(function() {
                mediaLibrary[index].name = newTitle;
                renderGallery();
            }).catch(function() {});
            return;
        }
        Swal.fire({
            title: 'Edit Title',
            input: 'text',
            inputValue: item.name || '',
            inputPlaceholder: 'Enter a title for this photo',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            confirmButtonText: 'Save',
        }).then(function(result) {
            if (!result.isConfirmed) return;
            fetch('/api/media/' + item.id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                body: JSON.stringify({
                    title: result.value
                })
            }).then(function() {
                mediaLibrary[index].name = result.value;
                renderGallery();
                Swal.fire({
                    title: 'Saved!',
                    icon: 'success',
                    timer: 1200,
                    showConfirmButton: false
                });
            }).catch(function() {});
        });
    }

    function deleteSingleMedia(index) {
        var item = mediaLibrary[index];
        if (!item) return;
        if (typeof Swal === 'undefined') {
            if (!confirm('Delete this photo?')) return;
        } else {
            Swal.fire({
                title: 'Delete photo?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: 'Delete',
            }).then(function(result) {
                if (!result.isConfirmed) return;
                doDeleteMedia([item.id], index);
            });
            return;
        }
        doDeleteMedia([item.id], index);
    }

    function doDeleteMedia(ids, index) {
        fetch('/api/media/delete', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({
                ids: ids
            })
        }).then(function() {
            loadMediaFromServer();
        }).catch(function() {});
    }

    window.editMediaTitle = editMediaTitle;
    window.deleteSingleMedia = deleteSingleMedia;

    function viewMedia(index) {
        currentMediaIndex = index;
        var item = mediaLibrary[index];
        var content = document.getElementById('viewerContent');
        if (!content) return;
        content.innerHTML = item.type === 'image' ?
            '<img src="' + item.src + '" alt="' + item.name + '">' :
            '<video src="' + item.src + '" controls autoplay></video>';
        var viewer = document.getElementById('mediaViewer');
        if (viewer) viewer.classList.add('active');
    }

    function closeViewer() {
        var viewer = document.getElementById('mediaViewer');
        if (viewer) viewer.classList.remove('active');
        var content = document.getElementById('viewerContent');
        if (content) content.innerHTML = '';
    }

    function editMedia() {
        Swal.fire({
            title: 'Edit Media',
            html: '<ul style="text-align:left;margin-left:20px;"><li>Crop &amp; Rotate</li><li>Filters &amp; Adjustments</li><li>Add Text &amp; Stickers</li><li>Drawing Tools</li></ul>',
            icon: 'info',
            confirmButtonColor: '#c9a96e',
            confirmButtonText: 'Open Editor'
        });
    }

    function downloadMedia() {
        var item = mediaLibrary[currentMediaIndex];
        if (!item) return;
        var link = document.createElement('a');
        link.href = item.src;
        link.download = item.name;
        link.click();
        Swal.fire({
            title: 'Downloaded!',
            text: 'Media saved to your device.',
            icon: 'success',
            confirmButtonColor: '#c9a96e',
            timer: 2000,
            showConfirmButton: false
        });
    }

    function shareMedia() {
        Swal.fire({
            title: 'Share Media',
            text: 'Choose how you want to share this media.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            confirmButtonText: 'Copy Link'
        });
    }

    function deleteMedia() {
        Swal.fire({
                title: 'Delete this media?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: 'Yes, delete it'
            })
            .then(function(result) {
                if (!result.isConfirmed) return;
                var item = mediaLibrary[currentMediaIndex];
                if (!item) return;
                fetch('/api/media/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken()
                    },
                    body: JSON.stringify({
                        ids: [item.id]
                    })
                }).then(function() {
                    closeViewer();
                    loadMediaFromServer();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Media has been removed.',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).catch(function() {});
            });
    }

    function selectAll() {
        selectedMedia = new Set(mediaLibrary.map(function(_, i) {
            return i;
        }));
        Swal.fire({
            title: 'All Selected',
            text: mediaLibrary.length + ' items selected.',
            icon: 'success',
            confirmButtonColor: '#c9a96e',
            timer: 1500,
            showConfirmButton: false
        });
    }

    function deleteSelected() {
        if (!selectedMedia.size) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select items first.',
                icon: 'warning',
                confirmButtonColor: '#c9a96e'
            });
            return;
        }
        Swal.fire({
                title: 'Delete ' + selectedMedia.size + ' items?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: 'Yes, delete them'
            })
            .then(function(result) {
                if (!result.isConfirmed) return;
                var ids = Array.from(selectedMedia).map(function(i) {
                    return mediaLibrary[i] && mediaLibrary[i].id;
                }).filter(Boolean);
                fetch('/api/media/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken()
                    },
                    body: JSON.stringify({
                        ids: ids
                    })
                }).then(function() {
                    selectedMedia.clear();
                    loadMediaFromServer();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Selected items removed.',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).catch(function() {});
            });
    }

    function shareSelected() {
        if (!selectedMedia.size) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select items first.',
                icon: 'warning',
                confirmButtonColor: '#c9a96e'
            });
            return;
        }
        Swal.fire({
            title: 'Share Selected',
            text: 'Share ' + selectedMedia.size + ' selected items.',
            icon: 'info',
            confirmButtonColor: '#c9a96e'
        });
    }

    function loadMediaFromServer() {
        fetch('/api/media', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                var items = (data.media || []).map(function(m) {
                    return {
                        id: m.id,
                        type: m.type,
                        src: m.url,
                        name: m.title || m.file_name || ('media-' + m.id),
                        date: m.created_at
                    };
                });
                mediaLibrary = items;
                renderGallery();
                updateMediaCounts();
            })
            .catch(function() {
                mediaLibrary = [];
                renderGallery();
                updateMediaCounts();
            });
    }

    function updateMediaCounts() {
        updateCounts({
            photos: mediaLibrary.length
        });
    }

    function uploadPhotos() {
        openGallery();
    }

    function viewProfile() {
        var cfg = (window.__dashboardConfig && window.__dashboardConfig.user) || {};
        var init = cfg.name ? cfg.name.split(' ').map(function(n) {
            return n[0];
        }).join('').toUpperCase().substring(0, 2) : 'U';
        var avatarHtml = cfg.avatar ?
            '<img src="' + cfg.avatar + '" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #c9a96e;">' :
            '<div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#c9a96e,#2c1810);color:white;display:inline-flex;align-items:center;justify-content:center;font-size:36px;font-weight:bold;">' + init + '</div>';
        Swal.fire({
            title: 'Your Profile',
            html: '<div style="text-align:center;margin-bottom:20px;">' + avatarHtml + '</div>' +
                '<div style="text-align:left;padding:0 20px;">' +
                '<p style="margin:10px 0;"><strong>Name:</strong> ' + (cfg.name || '—') + '</p>' +
                '<p style="margin:10px 0;"><strong>Type:</strong> ' + (cfg.type || 'Traveler') + '</p>' +
                '<p style="margin:10px 0;"><strong>Verified:</strong> ' + (cfg.verified ? '✅ Yes' : '❌ No') + '</p>' +
                '</div>',
            confirmButtonColor: '#c9a96e',
            confirmButtonText: 'Edit Profile',
            showCancelButton: true,
            cancelButtonText: 'Close',
        }).then(function(r) {
            if (r.isConfirmed) window.location.href = '/profile/edit';
        });
    }

    function openSettings() {
        Swal.fire({
            title: 'Settings',
            html: '<div style="text-align:left;padding:0 20px;">' +
                '<h4 style="margin-top:20px;color:#2c1810;">Account Settings</h4>' +
                '<p>• Update profile information</p><p>• Change password</p><p>• Privacy settings</p>' +
                '<h4 style="margin-top:20px;color:#2c1810;">Notification Preferences</h4>' +
                '<p>• Email notifications</p><p>• Push notifications</p>' +
                '<h4 style="margin-top:20px;color:#2c1810;">Travel Preferences</h4>' +
                '<p>• Default budget range</p><p>• Preferred destinations</p>' +
                '</div>',
            confirmButtonColor: '#c9a96e',
            confirmButtonText: 'Go to Settings',
            showCancelButton: true,
            cancelButtonText: 'Close',
        }).then(function(r) {
            if (r.isConfirmed) window.location.href = '/settings';
        });
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
            })
            .then(function(result) {
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

    function toggleSidebar() {
        var el = document.getElementById('sidebar');
        if (el) el.classList.toggle('active');
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.content;
        var match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    window.toggleNotifications = toggleNotifications;
    window.switchNotificationTab = switchNotificationTab;
    window.markAllRead = markAllRead;
    window.handleNotificationClick = handleNotificationClick;
    window.openComposeMessage = openComposeMessage;
    window.toggleSidebar = toggleSidebar;
    window.openGallery = openGallery;
    window.closeGallery = closeGallery;
    window.triggerFileInput = triggerFileInput;
    window.handleFileSelect = handleFileSelect;
    window.viewMedia = viewMedia;
    window.closeViewer = closeViewer;
    window.editMedia = editMedia;
    window.downloadMedia = downloadMedia;
    window.shareMedia = shareMedia;
    window.deleteMedia = deleteMedia;
    window.selectAll = selectAll;
    window.deleteSelected = deleteSelected;
    window.shareSelected = shareSelected;
    window.uploadPhotos = uploadPhotos;
    window.viewProfile = viewProfile;
    window.openSettings = openSettings;
    window.logout = logout;

}());