const userData = (window.__dashboardConfig && window.__dashboardConfig.user)
            ? window.__dashboardConfig.user
            : { name: '', firstName: '', avatar: '', type: '', verified: false, id: null };

        
        function initializeUserData() {
            
            const welcomeMsg = document.getElementById('welcomeMessage');
            welcomeMsg.textContent = `Welcome Back, ${userData.firstName}!`;

            
            if (userData.avatar && userData.avatar !== '') {
                
                const avatarImages = document.querySelectorAll('.user-avatar img, .nav-profile-pic img');
                avatarImages.forEach(img => {
                    if (img) {
                        img.src = userData.avatar;
                        img.style.display = 'block';
                    }
                });

                
                document.querySelectorAll('.avatar-placeholder, .placeholder').forEach(el => {
                    el.style.display = 'none';
                });
            } else {
                
                const initials = userData.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                const initialsElements = document.querySelectorAll('.avatar-placeholder, .placeholder');
                initialsElements.forEach(el => {
                    el.textContent = initials;
                    el.style.display = 'flex';
                });

                
                document.querySelectorAll('.user-avatar img, .nav-profile-pic img').forEach(img => {
                    if (img) img.style.display = 'none';
                });
            }

            
            document.getElementById('userName').textContent = userData.name;

            
            const userTypeBadge = document.getElementById('userTypeBadge');
            if (userTypeBadge) {
                userTypeBadge.className = `user-type-badge ${userData.type}`;
                const userTypeText = document.getElementById('userTypeText');
                if (userTypeText) {
                    userTypeText.textContent = userData.type.charAt(0).toUpperCase() + userData.type.slice(1);
                }
            }

            
            loadUserStatistics();
        }

        
        function loadUserStatistics() {
            
            fetch('/api/user/statistics')
                .then(response => response.json())
                .then(data => {
                    updateCounts(data);
                })
                .catch(error => {
                    console.log('Using default counts');
                    
                });
        }

        
        function updateCounts(data = null) {
            const photoCount = data?.photos || mediaLibrary.length;
            const tripsCount = data?.trips || 0;
            const bookingsCount = data?.bookings || 0;
            const savedCount = data?.saved || 0;
            const notificationCount = data?.notifications || 0;

            
            const photosCountEl = document.getElementById('photosCount');
            const statPhotosCountEl = document.getElementById('statPhotosCount');
            const bookingsCountEl = document.getElementById('bookingsCount');
            const statBookingsCountEl = document.getElementById('statBookingsCount');
            const savedCountEl = document.getElementById('savedCount');
            const statSavedCountEl = document.getElementById('statSavedCount');
            const statTripsCountEl = document.getElementById('statTripsCount');
            const notificationCountEl = document.getElementById('notificationCount');

            if (photosCountEl) photosCountEl.textContent = photoCount;
            if (statPhotosCountEl) statPhotosCountEl.textContent = photoCount;
            if (bookingsCountEl) bookingsCountEl.textContent = bookingsCount;
            if (statBookingsCountEl) statBookingsCountEl.textContent = bookingsCount;
            if (savedCountEl) savedCountEl.textContent = savedCount;
            if (statSavedCountEl) statSavedCountEl.textContent = savedCount;
            if (statTripsCountEl) statTripsCountEl.textContent = tripsCount;
            if (notificationCountEl) {
                notificationCountEl.textContent = notificationCount;
                notificationCountEl.style.display = notificationCount > 0 ? 'block' : 'none';
            }
        }

        
        let mediaLibrary = [];
        let selectedMedia = new Set();
        let currentMediaIndex = 0;

        
        let notifications = [];
        let currentTab = 'all';
        let unreadCount = 0;
        let pusherChannel = null;
        let chatPollingInterval = null;

        
        document.addEventListener('DOMContentLoaded', function () {
            initializeUserData();
            loadMediaFromStorage();
            loadNotifications();
            initializeRealTimeChat();

            
            setInterval(loadNotifications, 5000);
        });

        
        function initializeRealTimeChat() {
            const pusherKey = (window.__dashboardConfig && window.__dashboardConfig.pusherKey) || '';
            const pusherCluster = (window.__dashboardConfig && window.__dashboardConfig.pusherCluster) || 'mt1';
            const userId = (window.__dashboardConfig && window.__dashboardConfig.userId) || null;

            if (!pusherKey || !userId) {
                startChatPolling();
                return;
            }

            if (typeof Pusher !== 'undefined') {
                try {
                    const pusher = new Pusher(pusherKey, {
                        cluster: pusherCluster,
                        encrypted: true
                    });

                    pusherChannel = pusher.subscribe('private-user.' + userId);

                    pusherChannel.bind('new-chat-message', function(data) {
                        handleRealTimeChatMessage(data);
                    });

                    pusherChannel.bind('notification', function(data) {
                        handleRealTimeNotification(data);
                    });

                    console.log('Real-time chat initialized with Pusher');
                } catch (error) {
                    console.log('Pusher not available, using polling fallback');
                    startChatPolling();
                }
            } else {
                console.log('Pusher library not loaded, using polling fallback');
                startChatPolling();
            }
        }

        
        function startChatPolling() {
            
            chatPollingInterval = setInterval(() => {
                loadNotifications(true); 
            }, 2000);
        }

        
        function handleRealTimeChatMessage(data) {
            const newNotification = {
                id: data.message_id || Date.now(),
                type: 'chat',
                title: `New chat from ${data.sender_name}`,
                message: data.content,
                time: 'Just now',
                read: false,
                user: {
                    name: data.sender_name,
                    avatar: data.sender_avatar,
                    initials: data.sender_initials
                }
            };

            
            notifications.unshift(newNotification);
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
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: white;
                border: 2px solid var(--gold);
                border-radius: 12px;
                padding: 15px 20px;
                box-shadow: 0 8px 24px rgba(59, 31, 43, 0.2);
                z-index: 10000;
                min-width: 300px;
                max-width: 400px;
                animation: slideInRight 0.4s ease;
                cursor: pointer;
            `;

            const preview = message.length > 60 ? message.substring(0, 60) + '...' : message;

            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--gold), var(--deep)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px;">
                        ${sender.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: var(--deep); margin-bottom: 3px;">
                            <i class="fas fa-comments" style="color: var(--gold);"></i>
                            ${sender}
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted);">
                            ${preview}
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 16px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            toast.onclick = function() {
                window.location.href = '/chat';
                this.remove();
            };

            document.body.appendChild(toast);

            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.4s ease';
                setTimeout(() => toast.remove(), 400);
            }, 5000);
        }

        
        function playNotificationSound() {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBDGH0fPTgjMGHm7A7+OZUA0PVqzn77BdGgc+ltryxnYpBSh+zPLaizsIGGS57OihUxELTKXh8bllHAU2kNXzzn0vBSh6yfDajDwIFmq+7eibUg4OVKzl8LRfGgc8ldjywngqBCh9y/HajjwIFmm97OmgURALTqPi8bllHAU3kdXzzoAuBSh6yfDajjsJFWq97OmgUg0PVanl8LVfGgc8ldryw3kpBCd9y/DajjsJFWq+7OmfUhAMTqPh8bhnHgU3kdXzzn4vBCh6yfDajjsJFWq+7OidUREMTqPh8bhmHQU3kdXzzn4vBCd7yfDajjsJFmq97OmdUREMTqTg8bhmHQU3kdTzz34uBSd7yfDajjsJFmq97OmdUREMT6Th8bhpHgU2kNTzzoAuBSd7yfDbjTsIFmq97OicUhAMT6Tg8bppHgU2kNTzz4AuBSZ7yfDbkToJFWq97Omc');
                audio.volume = 0.3;
                audio.play().catch(() => {}); 
            } catch (e) {
                console.log('Could not play notification sound');
            }
        }

        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(400px); opacity: 0; }
                to   { transform: translateX(0);     opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0);     opacity: 1; }
                to   { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        
        function loadNotifications(silent = false) {
            
            fetch('/api/notifications')
                .then(response => response.json())
                .then(data => {
                    notifications = data.notifications || getSampleNotifications();
                    unreadCount = notifications.filter(n => !n.read).length;
                    updateNotificationBadge();
                    renderNotifications();
                })
                .catch(error => {
                    if (!silent) {
                        console.log('Using sample notifications');
                    }
                    notifications = getSampleNotifications();
                    unreadCount = notifications.filter(n => !n.read).length;
                    updateNotificationBadge();
                    renderNotifications();
                });
        }

        function getSampleNotifications() {
            return [
                {
                    id: 1,
                    type: 'chat',
                    title: 'New chat from Sarah Johnson',
                    message: 'Hey! I saw you\'re planning a trip to Bali. I have some great recommendations!',
                    time: '5 minutes ago',
                    read: false,
                    user: {
                        name: 'Sarah Johnson',
                        avatar: null,
                        initials: 'SJ'
                    }
                },
                {
                    id: 2,
                    type: 'booking',
                    title: 'Booking Confirmed',
                    message: 'Your flight to Tokyo has been confirmed. Check-in opens 24 hours before departure.',
                    time: '2 hours ago',
                    read: false
                },
                {
                    id: 3,
                    type: 'chat',
                    title: 'Michael Roberts sent you a chat',
                    message: 'Thanks for the travel tips! The restaurant you recommended was amazing.',
                    time: '5 hours ago',
                    read: true,
                    user: {
                        name: 'Michael Roberts',
                        avatar: null,
                        initials: 'MR'
                    }
                },
                {
                    id: 4,
                    type: 'trip',
                    title: 'Trip Reminder',
                    message: 'Your trip to Paris starts in 5 days. Don\'t forget to pack!',
                    time: '1 day ago',
                    read: false
                },
                {
                    id: 5,
                    type: 'photo',
                    title: 'Photos Uploaded',
                    message: 'Successfully uploaded 24 photos to your Bali album.',
                    time: '2 days ago',
                    read: true
                },
                {
                    id: 6,
                    type: 'chat',
                    title: 'Anna Chen mentioned you',
                    message: 'Anna Chen mentioned you in a chat: "You should check out this place!"',
                    time: '2 days ago',
                    read: true,
                    user: {
                        name: 'Anna Chen',
                        avatar: null,
                        initials: 'AC'
                    }
                },
                {
                    id: 7,
                    type: 'booking',
                    title: 'Price Drop Alert',
                    message: 'Good news! The hotel you saved in Santorini dropped by 25%.',
                    time: '3 days ago',
                    read: true
                },
                {
                    id: 8,
                    type: 'system',
                    title: 'Account Verified',
                    message: 'Congratulations! Your account has been successfully verified.',
                    time: '1 week ago',
                    read: true
                }
            ];
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');

            
            if (dropdown.classList.contains('active')) {
                setTimeout(() => {
                    markVisibleAsRead();
                }, 1000);
            }
        }

        function switchNotificationTab(tab) {
            currentTab = tab;

            
            document.querySelectorAll('.notification-tab').forEach(t => {
                t.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');

            
            renderNotifications();
        }

        function renderNotifications() {
            const listEl = document.getElementById('notificationList');
            let filteredNotifications = notifications;

            
            if (currentTab === 'chat') {
                filteredNotifications = notifications.filter(n => n.type === 'chat');
            } else if (currentTab === 'activity') {
                filteredNotifications = notifications.filter(n => n.type !== 'chat');
            }

            if (filteredNotifications.length === 0) {
                listEl.innerHTML = `
                    <div class="empty-notifications">
                        <i class="fas fa-bell-slash"></i>
                        <h4>No notifications</h4>
                        <p>You're all caught up!</p>
                    </div>
                `;
                return;
            }

            listEl.innerHTML = filteredNotifications.map(notif => {
                const iconClass = getNotificationIcon(notif.type);
                const userAvatar = notif.user ?
                    (notif.user.avatar ?
                        `<img src="${notif.user.avatar}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">` :
                        `<div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">${notif.user.initials}</div>`
                    ) :
                    `<i class="${iconClass}"></i>`;

                return `
                    <div class="notification-item ${notif.read ? '' : 'unread'}" onclick="handleNotificationClick(${notif.id})">
                        <div class="notification-icon-wrapper ${notif.type}">
                            ${userAvatar}
                        </div>
                        <div class="notification-content">
                            <h4>${notif.title}</h4>
                            <p>${notif.message}</p>
                            <div class="notification-time">
                                <i class="fas fa-clock"></i>
                                ${notif.time}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getNotificationIcon(type) {
            const icons = {
                'chat': 'fas fa-comments',
                'booking': 'fas fa-ticket-alt',
                'trip': 'fas fa-route',
                'photo': 'fas fa-images',
                'system': 'fas fa-info-circle'
            };
            return icons[type] || 'fas fa-bell';
        }

        function updateNotificationBadge() {
            const badge = document.getElementById('notificationCount');
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }

        function markAllRead() {
            notifications = notifications.map(n => ({...n, read: true}));
            unreadCount = 0;
            updateNotificationBadge();
            renderNotifications();

            
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).catch(console.error);

            Swal.fire({
                title: 'All notifications marked as read',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }

        function markVisibleAsRead() {
            const unreadNotifs = notifications.filter(n => !n.read);
            if (unreadNotifs.length === 0) return;

            const unreadIds = unreadNotifs.map(n => n.id);

            notifications = notifications.map(n =>
                unreadIds.includes(n.id) ? {...n, read: true} : n
            );

            unreadCount = notifications.filter(n => !n.read).length;
            updateNotificationBadge();
            renderNotifications();

            
            fetch('/api/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: unreadIds })
            }).catch(console.error);
        }

        function handleNotificationClick(notificationId) {
            const notification = notifications.find(n => n.id === notificationId);
            if (!notification) return;

            
            notifications = notifications.map(n =>
                n.id === notificationId ? {...n, read: true} : n
            );
            unreadCount = notifications.filter(n => !n.read).length;
            updateNotificationBadge();
            renderNotifications();

            
            if (notification.type === 'chat') {
                window.location.href = '/chat';
            } else if (notification.type === 'booking') {
                window.location.href = '/bookings';
            } else if (notification.type === 'trip') {
                window.location.href = '/plan-trip';
            } else if (notification.type === 'photo') {
                openGallery();
                toggleNotifications();
            }
        }

        
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const button = document.querySelector('.notification-btn');

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        
        function openComposeMessage() {
            Swal.fire({
                title: '<i class="fas fa-comments"></i> Send API Chat Message',
                html: `
                    <div style="text-align: left; padding: 10px 20px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--deep);">
                                <i class="fas fa-user"></i> To:
                            </label>
                            <input type="text" id="userSearch" placeholder="Search users..."
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px;"
                                oninput="searchUsers(this.value)">
                            <div id="userSearchResults" style="max-height: 150px; overflow-y: auto; margin-top: 10px; border: 1px solid var(--border); border-radius: 8px; display: none;">
                            </div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <div id="selectedUser" style="display: none; padding: 12px; background: rgba(201, 169, 110, 0.1); border-radius: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div id="selectedUserAvatar"></div>
                                    <div>
                                        <div id="selectedUserName" style="font-weight: 600; color: var(--deep);"></div>
                                        <div id="selectedUserType" style="font-size: 12px; color: var(--text-muted);"></div>
                                    </div>
                                    <button onclick="clearSelectedUser()" style="margin-left: auto; background: none; border: none; color: var(--danger); cursor: pointer; font-size: 18px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--deep);">
                                <i class="fas fa-comments"></i> Chat Message:
                            </label>
                            <textarea id="messageContent" placeholder="Type your chat message here..."
                                style="width: 100%; min-height: 120px; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Georgia', serif; resize: vertical;"
                                maxlength="1000"></textarea>
                            <div style="text-align: right; font-size: 12px; color: var(--text-muted); margin-top: 5px;">
                                <span id="charCount">0</span>/1000 characters
                            </div>
                        </div>
                        <input type="hidden" id="selectedUserId" value="">
                    </div>
                `,
                width: 600,
                showCancelButton: true,
                confirmButtonColor: '#c9a96e',
                cancelButtonColor: '#f44336',
                confirmButtonText: '<i class="fas fa-paper-plane"></i> Send Chat',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                didOpen: () => {
                    
                    const textarea = document.getElementById('messageContent');
                    textarea.addEventListener('input', function() {
                        document.getElementById('charCount').textContent = this.value.length;
                    });
                },
                preConfirm: () => {
                    const userId = document.getElementById('selectedUserId').value;
                    const message = document.getElementById('messageContent').value.trim();

                    if (!userId) {
                        Swal.showValidationMessage('Please select a user to chat with');
                        return false;
                    }

                    if (!message) {
                        Swal.showValidationMessage('Please enter a chat message');
                        return false;
                    }

                    if (message.length > 1000) {
                        Swal.showValidationMessage('Message is too long (max 1000 characters)');
                        return false;
                    }

                    return sendMessage(userId, message);
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Chat Message Sent!',
                        text: 'Your message has been delivered in real-time.',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000
                    });

                    
                    loadNotifications();
                }
            });
        }

        let searchTimeout;
        let availableUsers = [];

        function searchUsers(query) {
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                document.getElementById('userSearchResults').style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                
                fetch(`/api/users/search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        availableUsers = data.users || getSampleUsers().filter(u =>
                            u.name.toLowerCase().includes(query.toLowerCase())
                        );
                        displaySearchResults(availableUsers);
                    })
                    .catch(error => {
                        console.log('Using sample users');
                        availableUsers = getSampleUsers().filter(u =>
                            u.name.toLowerCase().includes(query.toLowerCase())
                        );
                        displaySearchResults(availableUsers);
                    });
            }, 300);
        }

        function getSampleUsers() {
            return [
                { id: 2, name: 'Sarah Johnson', type: 'traveler', avatar: null, verified: true },
                { id: 3, name: 'Michael Roberts', type: 'traveler', avatar: null, verified: false },
                { id: 4, name: 'Anna Chen', type: 'agency', avatar: null, verified: true },
                { id: 5, name: 'David Martinez', type: 'traveler', avatar: null, verified: true },
                { id: 6, name: 'Emily Wilson', type: 'agency', avatar: null, verified: true },
                { id: 7, name: 'James Brown', type: 'traveler', avatar: null, verified: false },
                { id: 8, name: 'Lisa Anderson', type: 'traveler', avatar: null, verified: true },
                { id: 9, name: 'Tom Smith', type: 'agency', avatar: null, verified: true }
            ];
        }

        function displaySearchResults(users) {
            const resultsDiv = document.getElementById('userSearchResults');

            if (users.length === 0) {
                resultsDiv.innerHTML = '<div style="padding: 15px; text-align: center; color: var(--text-muted);">No users found</div>';
                resultsDiv.style.display = 'block';
                return;
            }

            resultsDiv.innerHTML = users.map(user => {
                const initials = user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                const avatarHtml = user.avatar ?
                    `<img src="${user.avatar}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">` :
                    `<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">${initials}</div>`;

                const badge = user.type === 'agency' ?
                    '<span style="background: rgba(156, 39, 176, 0.1); color: var(--purple); padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;"><i class="fas fa-building"></i> Agency</span>' :
                    '';

                const verifiedBadge = user.verified ?
                    '<i class="fas fa-check-circle" style="color: var(--success); font-size: 12px; margin-left: 5px;"></i>' : '';

                return `
                    <div onclick="selectUser(${user.id})" style="padding: 12px; cursor: pointer; border-bottom: 1px solid var(--border); transition: all 0.3s ease; display: flex; align-items: center; gap: 12px;">
                        ${avatarHtml}
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: var(--deep); font-size: 14px;">
                                ${user.name}
                                ${verifiedBadge}
                                ${badge}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            resultsDiv.style.display = 'block';

            
            resultsDiv.querySelectorAll('div[onclick^="selectUser"]').forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.background = 'rgba(201, 169, 110, 0.1)';
                });
                el.addEventListener('mouseleave', function() {
                    this.style.background = 'transparent';
                });
            });
        }

        function selectUser(userId) {
            const user = availableUsers.find(u => u.id === userId);
            if (!user) return;

            const initials = user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
            const avatarHtml = user.avatar ?
                `<img src="${user.avatar}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">` :
                `<div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">${initials}</div>`;

            document.getElementById('selectedUserId').value = userId;
            document.getElementById('selectedUser').style.display = 'block';
            document.getElementById('selectedUserAvatar').innerHTML = avatarHtml;
            document.getElementById('selectedUserName').textContent = user.name;
            document.getElementById('selectedUserType').textContent = user.type === 'agency' ? 'Travel Agency' : 'Traveler';
            document.getElementById('userSearch').value = '';
            document.getElementById('userSearchResults').style.display = 'none';

            
            document.getElementById('messageContent').focus();
        }

        function clearSelectedUser() {
            document.getElementById('selectedUserId').value = '';
            document.getElementById('selectedUser').style.display = 'none';
            document.getElementById('userSearch').focus();
        }

        async function sendMessage(userId, message) {
            try {
                const response = await fetch('/api/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        receiver_id: userId,
                        content: message
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to send chat message');
                }

                const data = await response.json();

                
                const toast = document.createElement('div');
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
                    color: white;
                    padding: 15px 25px;
                    border-radius: 12px;
                    box-shadow: 0 8px 24px rgba(201, 169, 110, 0.4);
                    z-index: 10000;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    animation: slideInUp 0.4s ease;
                `;
                toast.innerHTML = `
                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    Chat message sent in real-time!
                `;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.animation = 'slideOutDown 0.4s ease';
                    setTimeout(() => toast.remove(), 400);
                }, 3000);

                return data;
            } catch (error) {
                console.error('Error sending chat message:', error);
                Swal.showValidationMessage('Failed to send chat. Please try again.');
                return false;
            }
        }

        
        const slideStyle = document.createElement('style');
        slideStyle.textContent = `

                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(100px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(slideStyle);

        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        
        function openGallery() {
            document.getElementById('galleryModal').classList.add('active');
            renderGallery();
        }

        function closeGallery() {
            document.getElementById('galleryModal').classList.remove('active');
        }

        function triggerFileInput() {
            document.getElementById('mediaInput').click();
        }

        
        function handleFileSelect(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const mediaItem = {
                        id: Date.now() + Math.random(),
                        type: file.type.startsWith('image/') ? 'image' : 'video',
                        src: e.target.result,
                        name: file.name,
                        date: new Date().toISOString()
                    };
                    mediaLibrary.push(mediaItem);
                    saveMediaToStorage();
                    renderGallery();
                    updateMediaCounts();
                };
                reader.readAsDataURL(file);
            });
            event.target.value = '';
        }

        
        const uploadArea = document.getElementById('uploadArea');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            });
        });

        uploadArea.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('mediaInput').files = files;
            handleFileSelect({ target: { files: files } });
        });

        
        function renderGallery() {
            const galleryGrid = document.getElementById('galleryGrid');
            if (mediaLibrary.length === 0) {
                galleryGrid.innerHTML = '';
                return;
            }

            galleryGrid.innerHTML = mediaLibrary.map((item, index) => `
                <div class="gallery-item" onclick="viewMedia(${index})">
                    ${item.type === 'image' ?
                    `<img src="${item.src}" alt="${item.name}">` :
                    `<video src="${item.src}"></video>
                        <div class="video-badge">
                            <i class="fas fa-play"></i>
                            Video
                        </div>`
                }
                </div>
            `).join('');
        }

        
        function viewMedia(index) {
            currentMediaIndex = index;
            const item = mediaLibrary[index];
            const viewerContent = document.getElementById('viewerContent');

            if (item.type === 'image') {
                viewerContent.innerHTML = `<img src="${item.src}" alt="${item.name}">`;
            } else {
                viewerContent.innerHTML = `<video src="${item.src}" controls autoplay></video>`;
            }

            document.getElementById('mediaViewer').classList.add('active');
        }

        function closeViewer() {
            document.getElementById('mediaViewer').classList.remove('active');
            const viewerContent = document.getElementById('viewerContent');
            viewerContent.innerHTML = '';
        }

        
        function editMedia() {
            Swal.fire({
                title: 'Edit Media',
                html: `
                    <div style="text-align: left;">
                        <p><strong>Editing Features:</strong></p>
                        <ul style="margin-left: 20px;">
                            <li>Crop & Rotate</li>
                            <li>Filters & Adjustments</li>
                            <li>Add Text & Stickers</li>
                            <li>Drawing Tools</li>
                        </ul>
                    </div>
                `,
                icon: 'info',
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Open Editor'
            });
        }

        function downloadMedia() {
            const item = mediaLibrary[currentMediaIndex];
            const link = document.createElement('a');
            link.href = item.src;
            link.download = item.name;
            link.click();
            Swal.fire({
                title: 'Downloaded!',
                text: 'Media has been saved to your device',
                icon: 'success',
                confirmButtonColor: '#c9a96e',
                timer: 2000
            });
        }

        function shareMedia() {
            Swal.fire({
                title: 'Share Media',
                text: 'Choose how you want to share this media',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Copy Link'
            });
        }

        function deleteMedia() {
            Swal.fire({
                title: 'Delete Media?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    mediaLibrary.splice(currentMediaIndex, 1);
                    saveMediaToStorage();
                    updateMediaCounts();
                    closeViewer();
                    renderGallery();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Media has been removed',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000
                    });
                }
            });
        }

        function selectAll() {
            selectedMedia = new Set(mediaLibrary.map((_, i) => i));
            Swal.fire({
                title: 'All Selected',
                text: `${mediaLibrary.length} items selected`,
                icon: 'success',
                confirmButtonColor: '#c9a96e',
                timer: 1500
            });
        }

        function deleteSelected() {
            if (selectedMedia.size === 0) {
                Swal.fire({
                    title: 'No Selection',
                    text: 'Please select items first',
                    icon: 'warning',
                    confirmButtonColor: '#c9a96e'
                });
                return;
            }

            Swal.fire({
                title: `Delete ${selectedMedia.size} items?`,
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: 'Yes, delete them'
            }).then((result) => {
                if (result.isConfirmed) {
                    mediaLibrary = mediaLibrary.filter((_, i) => !selectedMedia.has(i));
                    selectedMedia.clear();
                    saveMediaToStorage();
                    updateMediaCounts();
                    renderGallery();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Selected items have been removed',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        timer: 2000
                    });
                }
            });
        }

        function shareSelected() {
            Swal.fire({
                title: 'Share Selected',
                text: `Share ${selectedMedia.size} selected items`,
                icon: 'info',
                confirmButtonColor: '#c9a96e'
            });
        }

        
        function saveMediaToStorage() {
            localStorage.setItem('smartBookingMedia', JSON.stringify(mediaLibrary));
        }

        function loadMediaFromStorage() {
            const stored = localStorage.getItem('smartBookingMedia');
            if (stored) {
                mediaLibrary = JSON.parse(stored);
                updateMediaCounts();
            }
        }

        
        function updateMediaCounts() {
            const photoCount = mediaLibrary.length;

            
            const counts = {
                photos: photoCount,
                trips: 0,
                bookings: 0,
                saved: 0,
                notifications: photoCount > 0 ? 1 : 0
            };

            updateCounts(counts);
        }

        
        function uploadPhotos() {
            openGallery();
        }

        function viewProfile() {
            Swal.fire({
                title: 'Your Profile',
                html: `
                    <div style="text-align: center; margin-bottom: 20px;">
                        ${userData.avatar ?
                            `<img src="${userData.avatar}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--gold);">` :
                            `<div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">${userData.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}</div>`
                        }
                    </div>
                    <div style="text-align: left; padding: 0 20px;">
                        <p style="margin: 10px 0;"><strong>Name:</strong> ${userData.name}</p>
                        <p style="margin: 10px 0;"><strong>User Type:</strong> ${userData.type.charAt(0).toUpperCase() + userData.type.slice(1)}</p>
                        <p style="margin: 10px 0;"><strong>Verified:</strong> ${userData.verified ? '✅ Yes' : '❌ No'}</p>
                        <p style="margin: 10px 0;"><strong>User ID:</strong> ${userData.id || 'N/A'}</p>
                    </div>
                `,
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Edit Profile',
                showCancelButton: true,
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/profile/edit';
                }
            });
        }

        function openSettings() {
            Swal.fire({
                title: 'Settings',
                html: `
                    <div style="text-align: left; padding: 0 20px;">
                        <h4 style="margin-top: 20px;">Account Settings</h4>
                        <p>• Update profile information</p>
                        <p>• Change password</p>
                        <p>• Privacy settings</p>
                        <h4 style="margin-top: 20px;">Notification Preferences</h4>
                        <p>• Email notifications</p>
                        <p>• Push notifications</p>
                        <h4 style="margin-top: 20px;">Travel Preferences</h4>
                        <p>• Default budget range</p>
                        <p>• Preferred destinations</p>
                    </div>
                `,
                confirmButtonColor: '#c9a96e',
                confirmButtonText: 'Go to Settings',
                showCancelButton: true,
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/settings';
                }
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
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/logout';

                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken.getAttribute('content');
                        form.appendChild(csrfInput);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function (e) {
                
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                
                if (!this.onclick || this.getAttribute('href') === '#') {
                    document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        
        if (typeof toggleSidebar === 'function') window.toggleSidebar = toggleSidebar;
        if (typeof toggleNotifications === 'function') window.toggleNotifications = toggleNotifications;
        if (typeof switchNotificationTab === 'function') window.switchNotificationTab = switchNotificationTab;
        if (typeof markAllRead === 'function') window.markAllRead = markAllRead;
        if (typeof openComposeMessage === 'function') window.openComposeMessage = openComposeMessage;
        if (typeof viewProfile === 'function') window.viewProfile = viewProfile;
        if (typeof openSettings === 'function') window.openSettings = openSettings;
        if (typeof logout === 'function') window.logout = logout;

        if (typeof uploadPhotos === 'function') window.uploadPhotos = uploadPhotos;
        if (typeof openGallery === 'function') window.openGallery = openGallery;
        if (typeof closeGallery === 'function') window.closeGallery = closeGallery;
        if (typeof triggerFileInput === 'function') window.triggerFileInput = triggerFileInput;
        if (typeof handleFileSelect === 'function') window.handleFileSelect = handleFileSelect;
        if (typeof selectAll === 'function') window.selectAll = selectAll;
        if (typeof deleteSelected === 'function') window.deleteSelected = deleteSelected;
        if (typeof shareSelected === 'function') window.shareSelected = shareSelected;
        if (typeof viewMedia === 'function') window.viewMedia = viewMedia;
        if (typeof closeViewer === 'function') window.closeViewer = closeViewer;
        if (typeof editMedia === 'function') window.editMedia = editMedia;
        if (typeof downloadMedia === 'function') window.downloadMedia = downloadMedia;
        if (typeof shareMedia === 'function') window.shareMedia = shareMedia;
        if (typeof deleteMedia === 'function') window.deleteMedia = deleteMedia;