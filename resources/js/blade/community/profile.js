window.__COMMUNITY__ = (function () {
    var dc = window.__dashboardConfig || {};
    return {
        pusherKey:     dc.pusherKey     || '',
        pusherCluster: dc.pusherCluster || 'mt1',
        csrfToken:     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        authUserId:    dc.userId        || null,
        isLoggedIn:    !!dc.userId,
    };
})();

(function () {
    var cfg = window.__COMMUNITY__ || {};
    var profileUserId = window.__profileUserId;
    var currentTab = 'stories';

    function csrfToken() { return cfg.csrfToken || ''; }

    function apiFetch(url, opts) {
        opts = opts || {};
        opts.headers = Object.assign({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken()
        }, opts.headers || {});
        opts.credentials = 'same-origin';
        return fetch(url, opts).then(function (r) { return r.json(); });
    }

    function initials(name) {
        return (name || '').split(' ').map(function (w) { return w[0]; }).join('').toUpperCase().substring(0, 2);
    }

    function showToast(msg) {
        var t = document.getElementById('toast');
        var m = document.getElementById('toastMsg');
        if (!t || !m) return;
        m.textContent = msg;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    function formatDuration(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function loadProfile() {
        apiFetch('/api/users/' + profileUserId + '/profile').then(function (data) {
            var user = data.user;
            var stats = data.stats;

            // Update avatar
            var avatarEl = document.getElementById('profileAvatar');
            if (avatarEl) {
                if (user.avatar) {
                    avatarEl.innerHTML = '<img src="' + user.avatar + '" alt="' + user.name + '">';
                } else {
                    avatarEl.innerHTML = '<div class="avatar-initials">' + initials(user.name) + '</div>';
                }
            }

            // Update profile info
            document.getElementById('profileName').textContent = user.name;
            document.getElementById('profileBio').textContent = user.bio || 'Travel enthusiast exploring the world';
            
            var locationEl = document.getElementById('profileLocation');
            if (locationEl) {
                locationEl.innerHTML = '<i class="fas fa-map-marker-alt"></i> ' + (user.location || 'Somewhere on Earth');
            }

            var joinedEl = document.getElementById('profileJoined');
            if (joinedEl) {
                joinedEl.innerHTML = '<i class="fas fa-calendar"></i> Joined ' + user.created_at;
            }

            // Update stats
            document.getElementById('statStories').textContent = stats.stories || 0;
            document.getElementById('statTopics').textContent = stats.topics || 0;
            document.getElementById('statFollowers').textContent = stats.followers || 0;
            document.getElementById('statFollowing').textContent = stats.following || 0;

            // Add action buttons
            var actionsEl = document.getElementById('profileActions');
            if (actionsEl) {
                if (user.is_own_profile) {
                    actionsEl.innerHTML = 
                        '<button class="primary-button" onclick="window.location.href=\'/profile/edit\'">' +
                            '<i class="fas fa-edit"></i> Edit Profile' +
                        '</button>';
                    // Show create post box
                    document.getElementById('createPostBox').style.display = 'block';
                } else if (cfg.isLoggedIn) {
                    var followBtnClass = user.is_following ? 'secondary-button' : 'primary-button';
                    var followBtnText = user.is_following ? '<i class="fas fa-user-check"></i> Following' : '<i class="fas fa-user-plus"></i> Follow';
                    
                    actionsEl.innerHTML = 
                        '<button class="' + followBtnClass + '" id="followBtn" onclick="UserProfile.toggleFollow(' + user.id + ')">' +
                            followBtnText +
                        '</button>' +
                        '<button class="secondary-button" onclick="UserProfile.startChat(' + user.id + ')">' +
                            '<i class="fas fa-comment-dots"></i> Message' +
                        '</button>';
                }
            }

            // Load content
            renderStories(data.stories);
            renderTopics(data.topics);
            renderTrips(data.trips);
        }).catch(function () {
            showToast('Could not load profile');
        });
    }

    function renderStories(stories) {
        var el = document.getElementById('userStories');
        if (!el) return;

        if (!stories || !stories.length) {
            el.innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:40px;">No stories yet.</p>';
            return;
        }

        el.innerHTML = stories.map(function (s) {
            var isVideo = s.media_type === 'video';
            var mediaUrl = isVideo ? (s.thumbnail_url || s.video_url) : s.image_url;
            var likedClass = s.is_liked ? 'liked' : '';

            var videoOverlay = isVideo ? '<div class="video-overlay"><i class="fas fa-play-circle"></i>' + 
                (s.duration ? '<span class="video-duration">' + formatDuration(s.duration) + '</span>' : '') + 
                '</div>' : '';

            return '<div class="story-card" onclick="UserProfile.viewStory(' + s.id + ')">' +
                '<div class="story-img" style="background-image:url(\'' + mediaUrl + '\');cursor:pointer;position:relative;">' +
                    videoOverlay +
                '</div>' +
                '<div class="story-body">' +
                    '<h4>' + s.title + '</h4>' +
                    '<p>' + (s.excerpt || '') + '</p>' +
                    '<div class="story-footer">' +
                        '<button class="story-action-btn ' + likedClass + '" onclick="event.stopPropagation();UserProfile.likeStory(' + s.id + ',this)">' +
                            '<i class="fas fa-heart"></i> <span class="like-count">' + (s.likes || 0) + '</span>' +
                        '</button>' +
                        '<button class="story-action-btn">' +
                            '<i class="fas fa-comment"></i> ' + (s.comments || 0) +
                        '</button>' +
                        (isVideo ? '<button class="story-action-btn"><i class="fas fa-eye"></i> ' + (s.views || 0) + '</button>' : '') +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function renderTopics(topics) {
        var el = document.getElementById('userTopics');
        if (!el) return;

        if (!topics || !topics.length) {
            el.innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:40px;">No forum topics yet.</p>';
            return;
        }

        el.innerHTML = topics.map(function (t) {
            var tags = (t.tags || []).map(function (tag) { return '<span class="ft-tag">' + tag + '</span>'; }).join('');

            return '<div class="forum-topic">' +
                '<div class="ft-body">' +
                    '<h4 onclick="window.location.href=\'/community#topic-' + t.id + '\'">' + t.title + '</h4>' +
                    '<div class="ft-meta">' + t.created_at + '</div>' +
                    '<div style="margin-top:6px;">' + tags + '</div>' +
                    '<div class="ft-actions" style="display:flex;gap:8px;margin-top:8px;">' +
                        '<button class="story-action-btn">' +
                            '<i class="fas fa-heart"></i> ' + (t.likes || 0) +
                        '</button>' +
                        '<button class="story-action-btn">' +
                            '<i class="fas fa-comment"></i> ' + (t.replies_count || 0) +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function renderTrips(trips) {
        var el = document.getElementById('userTrips');
        if (!el) return;

        if (!trips || !trips.length) {
            el.innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:40px;">No trips yet.</p>';
            return;
        }

        el.innerHTML = trips.map(function (trip) {
            var statusClass = trip.status === 'completed' ? 'success' : 
                            trip.status === 'upcoming' ? 'warning' : 'info';

            return '<div class="trip-card">' +
                '<div class="trip-icon"><i class="fas fa-plane"></i></div>' +
                '<div class="trip-info">' +
                    '<h4>' + trip.destination + '</h4>' +
                    '<p>' + (trip.start_date || '') + (trip.end_date ? ' - ' + trip.end_date : '') + '</p>' +
                    '<span class="trip-status ' + statusClass + '">' + (trip.status || 'planned') + '</span>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function switchTab(tab) {
        currentTab = tab;

        // Update tab buttons
        var tabs = document.querySelectorAll('.profile-tab');
        tabs.forEach(function (t) {
            if (t.getAttribute('data-tab') === tab) {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });

        // Update tab content
        var contents = document.querySelectorAll('.profile-tab-content');
        contents.forEach(function (c) {
            if (c.id === 'tab-' + tab) {
                c.classList.add('active');
            } else {
                c.classList.remove('active');
            }
        });
    }

    function viewStory(storyId) {
        window.location.href = '/community?story=' + storyId;
    }

    function likeStory(id, btn) {
        if (!cfg.isLoggedIn) {
            showToast('Please login to like stories');
            return;
        }

        apiFetch('/api/community/stories/' + id + '/like', { method: 'POST' })
            .then(function(data) {
                if (btn) {
                    var countEl = btn.querySelector('.like-count');
                    if (countEl) countEl.textContent = data.likes || 0;
                    
                    if (data.liked) {
                        btn.classList.add('liked');
                    } else {
                        btn.classList.remove('liked');
                    }
                }
            })
            .catch(function() { showToast('Could not like story'); });
    }

    function startChat(userId) {
        window.location.href = '/chat/' + userId;
    }

    function toggleFollow(userId) {
        if (!cfg.isLoggedIn) {
            showToast('Please login to follow users');
            return;
        }

        var btn = document.getElementById('followBtn');
        if (btn) btn.disabled = true;

        apiFetch('/api/users/' + userId + '/follow', { method: 'POST' })
            .then(function(data) {
                if (data.is_following) {
                    btn.className = 'secondary-button';
                    btn.innerHTML = '<i class="fas fa-user-check"></i> Following';
                    showToast('Following!');
                } else {
                    btn.className = 'primary-button';
                    btn.innerHTML = '<i class="fas fa-user-plus"></i> Follow';
                    showToast('Unfollowed');
                }
                
                // Update follower count
                document.getElementById('statFollowers').textContent = data.followers_count || 0;
            })
            .catch(function() {
                showToast('Could not update follow status');
            })
            .finally(function() {
                if (btn) btn.disabled = false;
            });
    }

    function openStoryModal() {
        if (!cfg.isLoggedIn) {
            showToast('Please login to create stories');
            return;
        }
        document.getElementById('storyModal').classList.add('open');
    }

    function openTopicModal() {
        if (!cfg.isLoggedIn) {
            showToast('Please login to create topics');
            return;
        }
        document.getElementById('topicModal').classList.add('open');
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('open');
    }

    function toggleMediaType(type) {
        var imageGroup = document.getElementById('imageUrlGroup');
        var videoGroup = document.getElementById('videoUrlGroup');
        var thumbnailGroup = document.getElementById('thumbnailUrlGroup');
        var durationGroup = document.getElementById('durationGroup');

        if (type === 'video') {
            imageGroup.style.display = 'none';
            videoGroup.style.display = 'block';
            thumbnailGroup.style.display = 'block';
            durationGroup.style.display = 'block';
        } else {
            imageGroup.style.display = 'block';
            videoGroup.style.display = 'none';
            thumbnailGroup.style.display = 'none';
            durationGroup.style.display = 'none';
        }
    }

    function submitStory() {
        var title = (document.getElementById('storyTitle') || {}).value || '';
        var excerpt = (document.getElementById('storyExcerpt') || {}).value || '';
        var mediaType = document.querySelector('input[name="mediaType"]:checked').value;
        var imageUrl = (document.getElementById('storyImageUrl') || {}).value || '';
        var videoUrl = (document.getElementById('storyVideoUrl') || {}).value || '';
        var thumbnailUrl = (document.getElementById('storyThumbnailUrl') || {}).value || '';
        var duration = (document.getElementById('storyDuration') || {}).value || '';

        if (!title.trim()) {
            showToast('Please enter a title');
            return;
        }

        if (mediaType === 'image' && !imageUrl.trim()) {
            showToast('Please enter an image URL');
            return;
        }

        if (mediaType === 'video' && !videoUrl.trim()) {
            showToast('Please enter a video URL');
            return;
        }

        var btn = document.getElementById('submitStoryBtn');
        if (btn) btn.disabled = true;

        var payload = {
            title: title,
            excerpt: excerpt,
            media_type: mediaType,
            image_url: mediaType === 'image' ? imageUrl : null,
            video_url: mediaType === 'video' ? videoUrl : null,
            thumbnail_url: thumbnailUrl || null,
            duration: duration ? parseInt(duration) : null,
        };

        apiFetch('/api/community/stories', {
            method: 'POST',
            body: JSON.stringify(payload),
        }).then(function (response) {
            closeModal('storyModal');
            showToast(response.message || 'Story posted!');
            
            // Clear form
            ['storyTitle', 'storyExcerpt', 'storyImageUrl', 'storyVideoUrl', 'storyThumbnailUrl', 'storyDuration'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.querySelector('input[name="mediaType"][value="image"]').checked = true;
            toggleMediaType('image');
            
            // Reload profile
            loadProfile();
        }).catch(function () {
            showToast('Failed to post story');
        }).finally(function () {
            if (btn) btn.disabled = false;
        });
    }

    function submitTopic() {
        var title = (document.getElementById('topicTitle') || {}).value || '';
        var tags = (document.getElementById('topicTags') || {}).value || '';
        var body = (document.getElementById('topicBody') || {}).value || '';
        
        if (!title.trim() || !body.trim()) {
            showToast('Please fill in the required fields');
            return;
        }

        var btn = document.getElementById('submitTopicBtn');
        if (btn) btn.disabled = true;

        var tagArray = tags.split(',').map(function (t) { return t.trim(); }).filter(Boolean);

        apiFetch('/api/community/topics', {
            method: 'POST',
            body: JSON.stringify({ title: title, tags: tagArray, body: body }),
        }).then(function () {
            closeModal('topicModal');
            showToast('Topic posted!');
            
            // Clear form
            ['topicTitle', 'topicTags', 'topicBody'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.value = '';
            });
            
            // Reload profile
            loadProfile();
        }).catch(function () {
            showToast('Failed to post topic');
        }).finally(function () {
            if (btn) btn.disabled = false;
        });
    }

    document.addEventListener('click', function (e) {
        ['storyModal', 'topicModal'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && e.target === el) el.classList.remove('open');
        });
    });

    function init() {
        loadProfile();
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);

    window.UserProfile = {
        switchTab: switchTab,
        viewStory: viewStory,
        likeStory: likeStory,
        startChat: startChat,
        toggleFollow: toggleFollow,
        openStoryModal: openStoryModal,
        openTopicModal: openTopicModal,
        closeModal: closeModal,
        toggleMediaType: toggleMediaType,
        submitStory: submitStory,
        submitTopic: submitTopic,
    };

}());
