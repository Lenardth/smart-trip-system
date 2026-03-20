(function () {

    var mediaLibrary        = [];
    var selectedMedia       = new Set();
    var currentMediaIndex   = 0;
    var notifications       = [];
    var currentTab          = 'all';
    var unreadCount         = 0;
    var chatPollingInterval = null;
    var notifPollingInterval = null;
    var searchTimeout       = null;
    var availableUsers      = [];

    document.addEventListener('DOMContentLoaded', function () {
        initializeUserData();
        loadMediaFromStorage();
        loadNotifications();

        if (!notifPollingInterval) {
            notifPollingInterval = setInterval(loadNotifications, 5000);
        }

        initUploadArea();
        initMenuItems();
        initOutsideClickHandlers();
        initializeRealTimeChat();
    });

    function initUploadArea() {
        var uploadArea = document.getElementById('uploadArea');
        if (!uploadArea) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (ev) {
            uploadArea.addEventListener(ev, function (e) { e.preventDefault(); e.stopPropagation(); });
        });
        ['dragenter', 'dragover'].forEach(function (ev) {
            uploadArea.addEventListener(ev, function () { uploadArea.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            uploadArea.addEventListener(ev, function () { uploadArea.classList.remove('dragover'); });
        });
        uploadArea.addEventListener('drop', function (e) {
            handleFileSelect({ target: { files: e.dataTransfer.files } });
        });
    }

    function initMenuItems() {
        document.querySelectorAll('.menu-item').forEach(function (item) {
            item.addEventListener('click', function (e) {
                if (this.getAttribute('href') === '#') e.preventDefault();
                document.querySelectorAll('.menu-item').forEach(function (i) { i.classList.remove('active'); });
                this.classList.add('active');
            });
        });
    }

    function initOutsideClickHandlers() {
        document.addEventListener('click', function (event) {
            var sidebar = document.getElementById('sidebar');
            var toggle  = document.querySelector('.mobile-toggle');
            if (window.innerWidth <= 768 && sidebar && toggle) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
            var dropdown = document.getElementById('notificationDropdown');
            var bell     = document.querySelector('.notification-btn');
            if (dropdown && bell &&
                !dropdown.contains(event.target) &&
                !bell.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
    }

    function initializeRealTimeChat() {
        var cfg = window.__dashboardConfig || {};
        if (!cfg.pusherKey) {
            startChatPolling();
            return;
        }
        try {
            window.Echo = new LaravelEcho.default({
                broadcaster: 'pusher',
                key:         cfg.pusherKey,
                cluster:     cfg.pusherCluster,
                forceTLS:    true,
            });
            window.Echo.private('user.' + cfg.userId)
                .listen('NewChatMessage', handleRealTimeChatMessage)
                .listen('Notification',   handleRealTimeNotification);
        } catch (err) {
            startChatPolling();
        }
    }

    function startChatPolling() {
        if (chatPollingInterval) return;
        chatPollingInterval = setInterval(function () { loadNotifications(true); }, 3000);
    }

    function handleRealTimeChatMessage(data) {
        var notif = {
            id:      data.message_id || Date.now(),
            type:    'chat',
            title:   'New chat from ' + data.sender_name,
            message: data.content,
            time:    'Just now',
            read:    false,
            user: {
                name:     data.sender_name,
                avatar:   data.sender_avatar,
                initials: data.sender_initials,
            },
        };
        notifications.unshift(notif);
        unreadCount++;
        updateNotificationBadge();
        renderNotifications();
        playNotificationSound();
        showChatToast(data.sender_name, data.content);
    }

    function handleRealTimeNotification(data) {
        notifications.unshift(data);
        if (!data.read) unreadCount++;
        updateNotificationBadge();
        renderNotifications();
    }

    function showChatToast(sender, message) {
        var toast     = document.createElement('div');
        toast.className = 'chat-toast';
        var preview   = message.length > 60 ? message.substring(0, 60) + '…' : message;
        var initials  = sender.split(' ').map(function (n) { return n[0]; }).join('').toUpperCase().substring(0, 2);

        toast.innerHTML =
            '<div class="chat-toast-inner">' +
                '<div class="chat-toast-avatar">' + initials + '</div>' +
                '<div class="chat-toast-body">' +
                    '<div class="chat-toast-sender"><i class="fas fa-comments"></i> ' + sender + '</div>' +
                    '<div class="chat-toast-preview">' + preview + '</div>' +
                '</div>' +
                '<button class="chat-toast-close" onclick="this.closest(\'.chat-toast\').remove()">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>';

        toast.onclick = function () { window.location.href = '/chat'; toast.remove(); };
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.style.animation = 'slideOutRight 0.4s ease forwards';
            setTimeout(function () { toast.remove(); }, 400);
        }, 5000);
    }

    function playNotificationSound() {
        try {
            var ctx  = new (window.AudioContext || window.webkitAudioContext)();
            var osc  = ctx.createOscillator();
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
        fetch('/api/notifications')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                notifications = data.notifications || getSampleNotifications();
                unreadCount   = notifications.filter(function (n) { return !n.read; }).length;
                updateNotificationBadge();
                renderNotifications();
            })
            .catch(function () {
                if (!silent) {
                    notifications = getSampleNotifications();
                    unreadCount   = notifications.filter(function (n) { return !n.read; }).length;
                    updateNotificationBadge();
                    renderNotifications();
                }
            });
    }

    function getSampleNotifications() {
        return [
            { id:1, type:'chat',    title:'New chat from Sarah Johnson',     message:"Hey! I saw you're planning a trip to Bali. I have some great recommendations!", time:'5 minutes ago', read:false, user:{name:'Sarah Johnson',   avatar:null, initials:'SJ'} },
            { id:2, type:'booking', title:'Booking Confirmed',               message:'Your flight to Tokyo has been confirmed. Check-in opens 24 hours before departure.',  time:'2 hours ago',   read:false },
            { id:3, type:'chat',    title:'Michael Roberts sent you a chat',  message:'Thanks for the travel tips! The restaurant you recommended was amazing.',            time:'5 hours ago',   read:true,  user:{name:'Michael Roberts', avatar:null, initials:'MR'} },
            { id:4, type:'trip',    title:'Trip Reminder',                   message:"Your trip to Paris starts in 5 days. Don't forget to pack!",                          time:'1 day ago',     read:false },
            { id:5, type:'photo',   title:'Photos Uploaded',                 message:'Successfully uploaded 24 photos to your Bali album.',                                 time:'2 days ago',    read:true  },
            { id:6, type:'chat',    title:'Anna Chen mentioned you',          message:'Anna Chen mentioned you: "You should check out this place!"',                        time:'2 days ago',    read:true,  user:{name:'Anna Chen', avatar:null, initials:'AC'} },
            { id:7, type:'booking', title:'Price Drop Alert',                 message:'Good news! The hotel you saved in Santorini dropped by 25%.',                        time:'3 days ago',    read:true  },
            { id:8, type:'system',  title:'Account Verified',                 message:'Congratulations! Your account has been successfully verified.',                      time:'1 week ago',    read:true  },
        ];
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
        document.querySelectorAll('.notification-tab').forEach(function (t) { t.classList.remove('active'); });
        var el = document.querySelector('[data-tab="' + tab + '"]');
        if (el) el.classList.add('active');
        renderNotifications();
    }

    function renderNotifications() {
        var listEl = document.getElementById('notificationList');
        if (!listEl) return;

        var filtered = notifications;
        if (currentTab === 'chat')     filtered = notifications.filter(function (n) { return n.type === 'chat'; });
        if (currentTab === 'activity') filtered = notifications.filter(function (n) { return n.type !== 'chat'; });

        if (!filtered.length) {
            listEl.innerHTML =
                '<div class="empty-notifications">' +
                    '<i class="fas fa-bell-slash"></i>' +
                    '<h4>No notifications</h4>' +
                    '<p>You\'re all caught up!</p>' +
                '</div>';
            return;
        }

        listEl.innerHTML = filtered.map(function (notif) {
            var avatarHtml = notif.user
                ? (notif.user.avatar
                    ? '<img src="' + notif.user.avatar + '" style="width:45px;height:45px;border-radius:50%;object-fit:cover;">'
                    : '<div style="width:45px;height:45px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--deep));color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:16px;">' + notif.user.initials + '</div>')
                : '<i class="' + getNotificationIcon(notif.type) + '"></i>';

            return '<div class="notification-item ' + (notif.read ? '' : 'unread') + '" onclick="handleNotificationClick(' + notif.id + ')">' +
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
        var map = { chat:'fas fa-comments', booking:'fas fa-ticket-alt', trip:'fas fa-route', photo:'fas fa-images', system:'fas fa-info-circle' };
        return map[type] || 'fas fa-bell';
    }

    function updateNotificationBadge() {
        var badge = document.getElementById('notificationCount');
        if (!badge) return;
        if (unreadCount > 0) {
            badge.textContent   = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    function markAllRead() {
        notifications = notifications.map(function (n) { return Object.assign({}, n, { read: true }); });
        unreadCount   = 0;
        updateNotificationBadge();
        renderNotifications();
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        }).catch(console.error);
        Swal.fire({ title: 'All marked as read', icon: 'success', timer: 1500, showConfirmButton: false });
    }

    function markVisibleAsRead() {
        var unread = notifications.filter(function (n) { return !n.read; });
        if (!unread.length) return;
        var ids = unread.map(function (n) { return n.id; });
        notifications = notifications.map(function (n) {
            return ids.indexOf(n.id) !== -1 ? Object.assign({}, n, { read: true }) : n;
        });
        unreadCount = notifications.filter(function (n) { return !n.read; }).length;
        updateNotificationBadge();
        renderNotifications();
        fetch('/api/notifications/mark-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ ids: ids }),
        }).catch(console.error);
    }

    function handleNotificationClick(id) {
        var notif = notifications.find(function (n) { return n.id === id; });
        if (!notif) return;
        notifications = notifications.map(function (n) {
            return n.id === id ? Object.assign({}, n, { read: true }) : n;
        });
        unreadCount = notifications.filter(function (n) { return !n.read; }).length;
        updateNotificationBadge();
        renderNotifications();
        var routes = { chat: '/chat', booking: '/bookings', trip: '/plan-trip' };
        if (routes[notif.type])          window.location.href = routes[notif.type];
        else if (notif.type === 'photo') { openGallery(); toggleNotifications(); }
    }

    function openComposeMessage() {
        Swal.fire({
            title: '<i class="fas fa-comments"></i> Send a Chat Message',
            html:
                '<div style="text-align:left;padding:10px 20px;">' +
                    '<label style="display:block;margin-bottom:8px;font-weight:600;"><i class="fas fa-user"></i> To:</label>' +
                    '<input type="text" id="userSearch" placeholder="Search users…" style="width:100%;padding:12px;border:2px solid #e2d5c3;border-radius:8px;font-size:14px;" oninput="searchUsers(this.value)">' +
                    '<div id="userSearchResults" style="max-height:150px;overflow-y:auto;margin-top:10px;border:1px solid #e2d5c3;border-radius:8px;display:none;"></div>' +
                    '<div id="selectedUser" style="display:none;padding:12px;background:rgba(201,169,110,0.1);border-radius:8px;margin:10px 0;">' +
                        '<div style="display:flex;align-items:center;gap:12px;">' +
                            '<div id="selectedUserAvatar"></div>' +
                            '<div><div id="selectedUserName" style="font-weight:600;"></div><div id="selectedUserType" style="font-size:12px;color:#999;"></div></div>' +
                            '<button onclick="clearSelectedUser()" style="margin-left:auto;background:none;border:none;color:#e53935;cursor:pointer;font-size:18px;"><i class="fas fa-times"></i></button>' +
                        '</div>' +
                    '</div>' +
                    '<label style="display:block;margin:10px 0 8px;font-weight:600;"><i class="fas fa-comments"></i> Message:</label>' +
                    '<textarea id="messageContent" placeholder="Type your message…" style="width:100%;min-height:120px;padding:12px;border:2px solid #e2d5c3;border-radius:8px;font-size:14px;resize:vertical;" maxlength="1000" oninput="document.getElementById(\'charCount\').textContent=this.value.length"></textarea>' +
                    '<div style="text-align:right;font-size:12px;color:#999;margin-top:4px;"><span id="charCount">0</span>/1000</div>' +
                    '<input type="hidden" id="selectedUserId" value="">' +
                '</div>',
            width: 600,
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#f44336',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Send',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                var userId  = document.getElementById('selectedUserId').value;
                var message = document.getElementById('messageContent').value.trim();
                if (!userId)               { Swal.showValidationMessage('Please select a user');              return false; }
                if (!message)              { Swal.showValidationMessage('Please enter a message');            return false; }
                if (message.length > 1000) { Swal.showValidationMessage('Message too long (max 1000 chars)'); return false; }
                return sendMessage(userId, message);
            },
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                Swal.fire({ title: 'Message Sent!', text: 'Your message has been delivered.', icon: 'success', confirmButtonColor: '#c9a96e', timer: 2000, showConfirmButton: false });
                loadNotifications();
            }
        });
    }

    function searchUsers(query) {
        clearTimeout(searchTimeout);
        var resultsEl = document.getElementById('userSearchResults');
        if (query.length < 2) { if (resultsEl) resultsEl.style.display = 'none'; return; }

        searchTimeout = setTimeout(function () {
            fetch('/api/users/search?q=' + encodeURIComponent(query))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    availableUsers = data.users || [];
                    displaySearchResults(availableUsers);
                })
                .catch(function () {
                    availableUsers = getSampleUsers().filter(function (u) {
                        return u.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
                    });
                    displaySearchResults(availableUsers);
                });
        }, 300);
    }

    function getSampleUsers() {
        return [
            { id:2, name:'Sarah Johnson',   type:'traveler', avatar:null, verified:true  },
            { id:3, name:'Michael Roberts', type:'traveler', avatar:null, verified:false },
            { id:4, name:'Anna Chen',        type:'agency',   avatar:null, verified:true  },
            { id:5, name:'David Martinez',   type:'traveler', avatar:null, verified:true  },
            { id:6, name:'Emily Wilson',     type:'agency',   avatar:null, verified:true  },
            { id:7, name:'James Brown',      type:'traveler', avatar:null, verified:false },
            { id:8, name:'Lisa Anderson',    type:'traveler', avatar:null, verified:true  },
            { id:9, name:'Tom Smith',        type:'agency',   avatar:null, verified:true  },
        ];
    }

    function displaySearchResults(users) {
        var el = document.getElementById('userSearchResults');
        if (!el) return;
        if (!users.length) {
            el.innerHTML = '<div style="padding:15px;text-align:center;color:#999;">No users found</div>';
            el.style.display = 'block';
            return;
        }
        el.innerHTML = users.map(function (user) {
            var initials   = user.name.split(' ').map(function (n) { return n[0]; }).join('').toUpperCase().substring(0, 2);
            var avatarHtml = user.avatar
                ? '<img src="' + user.avatar + '" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">'
                : '<div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#c9a96e,#2c1810);color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;">' + initials + '</div>';
            var agencyBadge  = user.type === 'agency' ? '<span style="background:rgba(156,39,176,0.1);color:#9c27b0;padding:2px 8px;border-radius:4px;font-size:11px;margin-left:8px;"><i class="fas fa-building"></i> Agency</span>' : '';
            var verifiedIcon = user.verified ? '<i class="fas fa-check-circle" style="color:#43a047;font-size:12px;margin-left:5px;"></i>' : '';
            return '<div onclick="selectUser(' + user.id + ')" style="padding:12px;cursor:pointer;border-bottom:1px solid #e2d5c3;display:flex;align-items:center;gap:12px;" onmouseenter="this.style.background=\'rgba(201,169,110,0.1)\'" onmouseleave="this.style.background=\'\'">' +
                avatarHtml + '<div><strong style="font-size:14px;">' + user.name + verifiedIcon + agencyBadge + '</strong></div></div>';
        }).join('');
        el.style.display = 'block';
    }

    function selectUser(userId) {
        var user = availableUsers.find(function (u) { return u.id === userId; });
        if (!user) return;
        var initials   = user.name.split(' ').map(function (n) { return n[0]; }).join('').toUpperCase().substring(0, 2);
        var avatarHtml = user.avatar
            ? '<img src="' + user.avatar + '" style="width:45px;height:45px;border-radius:50%;object-fit:cover;">'
            : '<div style="width:45px;height:45px;border-radius:50%;background:linear-gradient(135deg,#c9a96e,#2c1810);color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:16px;">' + initials + '</div>';
        document.getElementById('selectedUserId').value          = userId;
        document.getElementById('selectedUser').style.display    = 'block';
        document.getElementById('selectedUserAvatar').innerHTML  = avatarHtml;
        document.getElementById('selectedUserName').textContent  = user.name;
        document.getElementById('selectedUserType').textContent  = user.type === 'agency' ? 'Travel Agency' : 'Traveler';
        document.getElementById('userSearch').value              = '';
        document.getElementById('userSearchResults').style.display = 'none';
        var mc = document.getElementById('messageContent');
        if (mc) mc.focus();
    }

    function clearSelectedUser() {
        document.getElementById('selectedUserId').value       = '';
        document.getElementById('selectedUser').style.display = 'none';
        var us = document.getElementById('userSearch');
        if (us) us.focus();
    }

    function sendMessage(userId, message) {
        return fetch('/api/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: JSON.stringify({ receiver_id: userId, content: message }),
        }).then(function (response) {
            if (!response.ok) throw new Error('Failed to send');
            return response.json();
        }).catch(function () {
            Swal.showValidationMessage('Failed to send. Please try again.');
            return false;
        });
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

    function loadUserStatistics() {
        var statsPromise = fetch('/api/user/statistics')
            .then(function (r) { return r.json(); })
            .catch(function () { return {}; });

        var wishlistPromise = fetch('/api/wishlist/count', {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .catch(function () { return { count: 0 }; });

        Promise.all([statsPromise, wishlistPromise])
            .then(function (results) {
                var data = results[0] || {};
                data.saved = (results[1] && results[1].count !== undefined)
                    ? results[1].count
                    : (data.saved || 0);
                updateCounts(data);
            });
    }

    function updateCounts(data) {
        data = data || {};
        var photos   = data.photos        !== undefined ? data.photos        : mediaLibrary.length;
        var trips    = data.trips         !== undefined ? data.trips         : 0;
        var bookings = data.bookings      !== undefined ? data.bookings      : 0;
        var saved    = data.saved         !== undefined ? data.saved         : 0;
        var notifs   = data.notifications !== undefined ? data.notifications : 0;

        function set(id, v) { var el = document.getElementById(id); if (el) el.textContent = v; }
        set('photosCount',       photos);
        set('statPhotosCount',   photos);
        set('bookingsCount',     bookings);
        set('statBookingsCount', bookings);
        set('savedCount',        saved);
        set('statSavedCount',    saved);
        set('statTripsCount',    trips);

        var badge = document.getElementById('notificationCount');
        if (badge) {
            badge.textContent   = notifs;
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
        Array.from(event.target.files).forEach(function (file) {
            var reader  = new FileReader();
            reader.onload = function (e) {
                mediaLibrary.push({
                    id:   Date.now() + Math.random(),
                    type: file.type.indexOf('image/') === 0 ? 'image' : 'video',
                    src:  e.target.result,
                    name: file.name,
                    date: new Date().toISOString(),
                });
                saveMediaToStorage();
                renderGallery();
                updateMediaCounts();
            };
            reader.readAsDataURL(file);
        });
        if (event.target && event.target.value !== undefined) event.target.value = '';
    }

    function renderGallery() {
        var grid = document.getElementById('galleryGrid');
        if (!grid) return;
        grid.innerHTML = mediaLibrary.map(function (item, i) {
            return '<div class="gallery-item" onclick="viewMedia(' + i + ')">' +
                (item.type === 'image'
                    ? '<img src="' + item.src + '" alt="' + item.name + '">'
                    : '<video src="' + item.src + '"></video><div class="video-badge"><i class="fas fa-play"></i> Video</div>') +
            '</div>';
        }).join('');
    }

    function viewMedia(index) {
        currentMediaIndex = index;
        var item    = mediaLibrary[index];
        var content = document.getElementById('viewerContent');
        if (!content) return;
        content.innerHTML = item.type === 'image'
            ? '<img src="' + item.src + '" alt="' + item.name + '">'
            : '<video src="' + item.src + '" controls autoplay></video>';
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
        Swal.fire({ title: 'Edit Media', html: '<ul style="text-align:left;margin-left:20px;"><li>Crop &amp; Rotate</li><li>Filters &amp; Adjustments</li><li>Add Text &amp; Stickers</li><li>Drawing Tools</li></ul>', icon: 'info', confirmButtonColor: '#c9a96e', confirmButtonText: 'Open Editor' });
    }

    function downloadMedia() {
        var item = mediaLibrary[currentMediaIndex];
        if (!item) return;
        var link    = document.createElement('a');
        link.href     = item.src;
        link.download = item.name;
        link.click();
        Swal.fire({ title: 'Downloaded!', text: 'Media saved to your device.', icon: 'success', confirmButtonColor: '#c9a96e', timer: 2000, showConfirmButton: false });
    }

    function shareMedia() {
        Swal.fire({ title: 'Share Media', text: 'Choose how you want to share this media.', icon: 'info', showCancelButton: true, confirmButtonColor: '#c9a96e', confirmButtonText: 'Copy Link' });
    }

    function deleteMedia() {
        Swal.fire({ title: 'Delete this media?', text: 'This action cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#f44336', cancelButtonColor: '#6b5b4f', confirmButtonText: 'Yes, delete it' })
        .then(function (result) {
            if (!result.isConfirmed) return;
            mediaLibrary.splice(currentMediaIndex, 1);
            saveMediaToStorage();
            updateMediaCounts();
            closeViewer();
            renderGallery();
            Swal.fire({ title: 'Deleted!', text: 'Media has been removed.', icon: 'success', confirmButtonColor: '#c9a96e', timer: 2000, showConfirmButton: false });
        });
    }

    function selectAll() {
        selectedMedia = new Set(mediaLibrary.map(function (_, i) { return i; }));
        Swal.fire({ title: 'All Selected', text: mediaLibrary.length + ' items selected.', icon: 'success', confirmButtonColor: '#c9a96e', timer: 1500, showConfirmButton: false });
    }

    function deleteSelected() {
        if (!selectedMedia.size) { Swal.fire({ title: 'No Selection', text: 'Please select items first.', icon: 'warning', confirmButtonColor: '#c9a96e' }); return; }
        Swal.fire({ title: 'Delete ' + selectedMedia.size + ' items?', text: 'This action cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#f44336', cancelButtonColor: '#6b5b4f', confirmButtonText: 'Yes, delete them' })
        .then(function (result) {
            if (!result.isConfirmed) return;
            mediaLibrary = mediaLibrary.filter(function (_, i) { return !selectedMedia.has(i); });
            selectedMedia.clear();
            saveMediaToStorage();
            updateMediaCounts();
            renderGallery();
            Swal.fire({ title: 'Deleted!', text: 'Selected items removed.', icon: 'success', confirmButtonColor: '#c9a96e', timer: 2000, showConfirmButton: false });
        });
    }

    function shareSelected() {
        if (!selectedMedia.size) { Swal.fire({ title: 'No Selection', text: 'Please select items first.', icon: 'warning', confirmButtonColor: '#c9a96e' }); return; }
        Swal.fire({ title: 'Share Selected', text: 'Share ' + selectedMedia.size + ' selected items.', icon: 'info', confirmButtonColor: '#c9a96e' });
    }

    function saveMediaToStorage()   { try { localStorage.setItem('smartBookingMedia', JSON.stringify(mediaLibrary)); } catch(_) {} }
    function loadMediaFromStorage() { try { var s = localStorage.getItem('smartBookingMedia'); if (s) { mediaLibrary = JSON.parse(s); updateMediaCounts(); } } catch (_) {} }
    function updateMediaCounts()    { updateCounts({ photos: mediaLibrary.length }); }
    function uploadPhotos()         { openGallery(); }

    function viewProfile() {
        var cfg  = (window.__dashboardConfig && window.__dashboardConfig.user) || {};
        var init = cfg.name ? cfg.name.split(' ').map(function (n) { return n[0]; }).join('').toUpperCase().substring(0, 2) : 'U';
        var avatarHtml = cfg.avatar
            ? '<img src="' + cfg.avatar + '" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #c9a96e;">'
            : '<div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#c9a96e,#2c1810);color:white;display:inline-flex;align-items:center;justify-content:center;font-size:36px;font-weight:bold;">' + init + '</div>';
        Swal.fire({
            title: 'Your Profile',
            html: '<div style="text-align:center;margin-bottom:20px;">' + avatarHtml + '</div>' +
                '<div style="text-align:left;padding:0 20px;">' +
                    '<p style="margin:10px 0;"><strong>Name:</strong> ' + (cfg.name || '—') + '</p>' +
                    '<p style="margin:10px 0;"><strong>Type:</strong> ' + (cfg.type || 'Traveler') + '</p>' +
                    '<p style="margin:10px 0;"><strong>Verified:</strong> ' + (cfg.verified ? '✅ Yes' : '❌ No') + '</p>' +
                '</div>',
            confirmButtonColor: '#c9a96e',
            confirmButtonText:  'Edit Profile',
            showCancelButton:   true,
            cancelButtonText:   'Close',
        }).then(function (r) { if (r.isConfirmed) window.location.href = '/profile/edit'; });
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
            confirmButtonText:  'Go to Settings',
            showCancelButton:   true,
            cancelButtonText:   'Close',
        }).then(function (r) { if (r.isConfirmed) window.location.href = '/settings'; });
    }

    function logout() {
        Swal.fire({ title: 'Logout', text: 'Are you sure you want to logout?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#c9a96e', cancelButtonColor: '#f44336', confirmButtonText: 'Yes, logout' })
        .then(function (result) {
            if (!result.isConfirmed) return;
            var form  = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            var csrf  = document.createElement('input');
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

    window.toggleNotifications     = toggleNotifications;
    window.switchNotificationTab   = switchNotificationTab;
    window.markAllRead             = markAllRead;
    window.handleNotificationClick = handleNotificationClick;
    window.openComposeMessage      = openComposeMessage;
    window.searchUsers             = searchUsers;
    window.selectUser              = selectUser;
    window.clearSelectedUser       = clearSelectedUser;
    window.toggleSidebar           = toggleSidebar;
    window.openGallery             = openGallery;
    window.closeGallery            = closeGallery;
    window.triggerFileInput        = triggerFileInput;
    window.handleFileSelect        = handleFileSelect;
    window.viewMedia               = viewMedia;
    window.closeViewer             = closeViewer;
    window.editMedia               = editMedia;
    window.downloadMedia           = downloadMedia;
    window.shareMedia              = shareMedia;
    window.deleteMedia             = deleteMedia;
    window.selectAll               = selectAll;
    window.deleteSelected          = deleteSelected;
    window.shareSelected           = shareSelected;
    window.uploadPhotos            = uploadPhotos;
    window.viewProfile             = viewProfile;
    window.openSettings            = openSettings;
    window.logout                  = logout;

}());
