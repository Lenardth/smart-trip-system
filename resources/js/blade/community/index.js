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

    var cfg          = window.__COMMUNITY__ || {};
    var inviteTarget = null;

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

    function avatar(user, size) {
        size = size || 44;
        var s = 'width:' + size + 'px;height:' + size + 'px;';
        if (user && user.avatar) {
            return '<div style="' + s + 'border-radius:50%;overflow:hidden;flex-shrink:0;"><img src="' + user.avatar + '" style="width:100%;height:100%;object-fit:cover;"></div>';
        }
        return '<div style="' + s + 'border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--deep));color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:' + Math.round(size * 0.36) + 'px;flex-shrink:0;">' + initials(user && user.name || 'U') + '</div>';
    }

    function showToast(msg) {
        var t = document.getElementById('toast');
        var m = document.getElementById('toastMsg');
        if (!t || !m) return;
        m.textContent = msg;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    function requireLogin(action) {
        if (cfg.isLoggedIn) return true;
        Swal.fire({
            title: 'Login Required',
            html: 'You need to be logged in to ' + (action || 'use chat') + '.<br><br>' +
                  '<a href="/login" style="color:var(--gold);font-weight:700;">Sign in</a> &nbsp;·&nbsp; ' +
                  '<a href="/register" style="color:var(--gold);font-weight:700;">Create account</a>',
            icon: 'info',
            confirmButtonColor: '#c9a96e',
            confirmButtonText: 'Go to Login',
        }).then(function (r) {
            if (r.isConfirmed) window.location.href = '/login';
        });
        return false;
    }

    function startChat(userId, e) {
        if (e) e.preventDefault();
        if (!requireLogin('send messages')) return;
        window.location.href = '/chat/' + userId;
    }

    function messageBtn(userId, userName) {
        if (!userId || userId === cfg.authUserId) return '';
        return '<button class="msg-btn" onclick="Community.startChat(' + userId + ',event)">' +
            '<i class="fas fa-comment-dots"></i> Message' +
        '</button>';
    }

    function inviteBtn(userId, userName, userAvatar, sub) {
        if (!userId || userId === cfg.authUserId) return '';
        var encoded = encodeURIComponent(JSON.stringify({ id: userId, name: userName, avatar: userAvatar || '', sub: sub || '' }));
        return '<button class="invite-btn" onclick="Community.openInviteModal(\'' + encoded + '\')">' +
            '<i class="fas fa-paper-plane"></i> Invite' +
        '</button>';
    }

    function loadStats() {
        apiFetch('/api/community/stats').then(function (data) {
            function bump(id, val) {
                var el = document.getElementById(id);
                if (!el) return;
                el.innerHTML = val;
                el.classList.add('bump');
                setTimeout(function () { el.classList.remove('bump'); }, 600);
            }
            bump('stat-members', data.members || 0);
            bump('stat-stories', data.stories || 0);
            bump('stat-groups',  data.groups  || 0);
            bump('stat-topics',  data.topics  || 0);
        }).catch(function () {});
    }

    function loadTopics() {
        apiFetch('/api/community/topics').then(function (data) {
            var el = document.getElementById('forumTopics');
            if (!el) return;
            var topics = data.topics || data || [];
            if (!topics.length) {
                el.innerHTML = '<p style="padding:20px;text-align:center;color:var(--text-muted);">No topics yet. Start the conversation!</p>';
                return;
            }
            el.innerHTML = topics.map(function (t) {
                var tags       = (t.tags || []).map(function (tag) { return '<span class="ft-tag">' + tag + '</span>'; }).join('');
                var authorId   = t.user_id || null;
                var authorName = t.author  || 'Traveler';
                var msgBtn     = cfg.isLoggedIn ? messageBtn(authorId, authorName) : '';
                var invBtn     = cfg.isLoggedIn ? inviteBtn(authorId, authorName, t.author_avatar, 'Forum member') : '';
                var avatarHtml = t.author_avatar
                    ? '<div class="forum-avatar" style="overflow:hidden;"><img src="' + t.author_avatar + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"></div>'
                    : '<div class="forum-avatar">' + initials(authorName) + '</div>';

                return '<div class="forum-topic">' +
                    avatarHtml +
                    '<div class="ft-body">' +
                        '<h4 onclick="Community.openTopic(' + t.id + ')">' + t.title + '</h4>' +
                        '<div class="ft-meta">' +
                            '<span>by <strong>' + authorName + '</strong></span> · ' + (t.created_at || '') +
                        '</div>' +
                        '<div style="margin-top:6px;">' + tags + '</div>' +
                        '<div class="ft-actions" style="display:flex;gap:8px;margin-top:8px;align-items:center;">' +
                            '<button class="story-action-btn" onclick="Community.likeTopic(' + t.id + ',this)">' +
                                '<i class="fas fa-heart"></i> <span class="like-count">' + (t.likes || 0) + '</span>' +
                            '</button>' +
                            '<button class="story-action-btn" onclick="Community.openTopic(' + t.id + ')">' +
                                '<i class="fas fa-comment"></i> Reply' +
                            '</button>' +
                            (msgBtn || invBtn ? msgBtn + invBtn : '') +
                        '</div>' +
                    '</div>' +
                    '<div class="ft-stats">' +
                        '<div class="fs-num">' + (t.replies_count || 0) + '</div>' +
                        '<div class="fs-label">replies</div>' +
                    '</div>' +
                '</div>';
            }).join('');
        }).catch(function () {});
    }

    function loadGroups() {
        apiFetch('/api/community/groups').then(function (data) {
            var el = document.getElementById('groupTrips');
            if (!el) return;
            var groups = data.groups || data || [];
            if (!groups.length) {
                el.innerHTML = '<p style="text-align:center;color:var(--text-muted);font-size:13px;">No group trips yet.</p>';
                return;
            }
            el.innerHTML = groups.map(function (g) {
                var full     = g.spots_left <= 0;
                var badgeCls = full ? 'gt-badge full' : 'gt-badge';
                var badgeTxt = full ? 'Full' : g.spots_left + ' spots left';
                var orgId    = g.user_id || null;
                var orgName  = g.organizer || 'Organizer';
                var msgBtn   = cfg.isLoggedIn ? messageBtn(orgId, orgName) : '';
                var joinBtn  = cfg.isLoggedIn && !full ? '<button class="primary-button" style="font-size:12px;padding:6px 12px;margin-top:6px;" onclick="Community.joinGroup(' + g.id + ',this)"><i class="fas fa-user-plus"></i> Join</button>' : '';

                return '<div class="group-trip">' +
                    '<div class="gt-icon"><i class="fas fa-map-marked-alt"></i></div>' +
                    '<div class="gt-info">' +
                        '<h4>' + g.name + '</h4>' +
                        '<p>' + (g.destination || '') + (g.date ? ' · ' + g.date : '') + '</p>' +
                        '<div style="display:flex;gap:8px;margin-top:6px;">' +
                            (msgBtn || '') +
                            (joinBtn || '') +
                        '</div>' +
                    '</div>' +
                    '<span class="' + badgeCls + '">' + badgeTxt + '</span>' +
                '</div>';
            }).join('');
        }).catch(function () {});
    }

    function joinGroup(groupId, btn) {
        if (!requireLogin('join groups')) return;
        
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Joining...';
        }
        
        apiFetch('/api/community/groups/' + groupId + '/join', { method: 'POST' })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message || 'Joined group!');
                    loadGroups(); // Refresh groups
                    loadStats();  // Update stats
                } else {
                    showToast(data.message || 'Could not join group');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-user-plus"></i> Join';
                    }
                }
            })
            .catch(function(err) {
                showToast('Failed to join group');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-user-plus"></i> Join';
                }
            });
    }

    function loadTags() {
        apiFetch('/api/community/tags').then(function (data) {
            var el = document.getElementById('trendingTags');
            if (!el) return;
            var tags = data.tags || data || [];
            el.innerHTML = tags.map(function (tag) {
                var name = tag.name || tag;
                return '<button class="tag-item">#' + name + '</button>';
            }).join('');
        }).catch(function () {});
    }

    function loadStories() {
        apiFetch('/api/community/stories').then(function (data) {
            var el = document.getElementById('storiesGrid');
            if (!el) return;
            var stories = data.stories || data || [];
            if (!stories.length) {
                el.innerHTML = '<p style="text-align:center;color:var(--text-muted);">No stories yet.</p>';
                return;
            }
            el.innerHTML = stories.map(function (s) {
                var authorId   = s.user_id || null;
                var authorName = s.author  || 'Traveler';
                var isOwnPost  = authorId === cfg.authUserId;
                var msgBtn     = cfg.isLoggedIn && !isOwnPost ? messageBtn(authorId, authorName) : '';
                var isVideo    = s.media_type === 'video';
                var mediaUrl   = isVideo ? (s.thumbnail_url || s.video_url) : (s.image_url || s.image);
                var likedClass = s.is_liked ? 'liked' : '';

                var videoOverlay = isVideo ? '<div class="video-overlay"><i class="fas fa-play-circle"></i>' + 
                    (s.duration ? '<span class="video-duration">' + formatDuration(s.duration) + '</span>' : '') + 
                    '</div>' : '';

                var authorLink = authorId ? '<strong style="cursor:pointer;" onclick="Community.viewProfile(' + authorId + ')">' + authorName + '</strong>' : '<strong>' + authorName + '</strong>';

                return '<div class="story-card" onclick="Community.viewStory(' + s.id + ')">' +
                    '<div class="story-img" style="background-image:url(\'' + mediaUrl + '\');cursor:pointer;position:relative;">' +
                        videoOverlay +
                    '</div>' +
                    '<div class="story-body">' +
                        '<div class="story-author">' +
                            avatar({ name: authorName, avatar: s.author_avatar }, 34) +
                            '<div class="sa-info">' +
                                authorLink +
                                (s.created_at || '') +
                            '</div>' +
                            (msgBtn ? '<div style="margin-left:auto;">' + msgBtn + '</div>' : '') +
                        '</div>' +
                        '<h4>' + s.title + '</h4>' +
                        '<p>' + (s.excerpt || '') + '</p>' +
                        '<div class="story-footer">' +
                            '<button class="story-action-btn ' + likedClass + '" onclick="event.stopPropagation();Community.likeStory(' + s.id + ',this)">' +
                                '<i class="fas fa-heart"></i> <span class="like-count">' + (s.likes || 0) + '</span>' +
                            '</button>' +
                            '<button class="story-action-btn" onclick="event.stopPropagation();Community.openStoryComments(' + s.id + ',\'' + (s.title || '').replace(/'/g, "\\'") + '\')">' +
                                '<i class="fas fa-comment"></i> ' + (s.comments || 0) +
                            '</button>' +
                            (isVideo ? '<button class="story-action-btn"><i class="fas fa-eye"></i> ' + (s.views || 0) + '</button>' : '') +
                        '</div>' +
                    '</div>' +
                '</div>';
            }).join('');
        }).catch(function () {});
    }

    function formatDuration(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function loadTravelers() {
        apiFetch('/api/community/travelers').then(function (data) {
            var el = document.getElementById('travelersGrid');
            if (!el) return;
            var travelers = data.travelers || data || [];
            if (!travelers.length) {
                el.innerHTML = '<p style="text-align:center;color:var(--text-muted);">No travelers yet.</p>';
                return;
            }
            el.innerHTML = travelers.map(function (t) {
                var userId = t.id || null;
                var isOwnProfile = userId === cfg.authUserId;
                var msgBtn = cfg.isLoggedIn && !isOwnProfile ? messageBtn(userId, t.name) : '';
                var invBtn = cfg.isLoggedIn && !isOwnProfile ? inviteBtn(userId, t.name, t.avatar, t.location || 'Traveler') : '';
                var av     = t.avatar
                    ? '<img src="' + t.avatar + '" style="width:100%;height:100%;object-fit:cover;">'
                    : initials(t.name);

                return '<div class="traveler-card" onclick="Community.viewProfile(' + userId + ')" style="cursor:pointer;">' +
                    '<div class="traveler-avatar">' + av + '</div>' +
                    '<h4>' + t.name + '</h4>' +
                    '<p class="tc-sub">' + (t.location || t.bio || 'Traveler') + '</p>' +
                    '<div class="tc-stats">' +
                        '<div class="tc-stat"><div class="ts-num">' + (t.trips || 0) + '</div><div class="ts-label">Trips</div></div>' +
                        '<div class="tc-stat"><div class="ts-num">' + (t.countries || 0) + '</div><div class="ts-label">Countries</div></div>' +
                    '</div>' +
                    (t.badge ? '<div class="tc-badge">' + t.badge + '</div>' : '') +
                    '<div class="tc-actions" onclick="event.stopPropagation();">' + msgBtn + invBtn + '</div>' +
                '</div>';
            }).join('');
        }).catch(function () {});
    }

    function openTopic(id) {
        apiFetch('/api/community/topics/' + id).then(function (data) {
            var topic     = data.topic || data;
            
            function renderReplies(replies) {
                return (replies || []).map(function (r) {
                    var rUserId = r.user_id || null;
                    var rName   = r.author  || 'Traveler';
                    var msgBtn  = cfg.isLoggedIn ? messageBtn(rUserId, rName) : '';
                    return '<div class="reply-item">' +
                        '<div class="forum-avatar" style="width:34px;height:34px;font-size:12px;">' + initials(rName) + '</div>' +
                        '<div class="reply-body">' +
                            '<div class="reply-author"><strong>' + rName + '</strong> ' + (r.created_at || '') +
                                (msgBtn ? ' <span style="margin-left:8px;">' + msgBtn + '</span>' : '') +
                            '</div>' +
                            '<p>' + r.body + '</p>' +
                        '</div>' +
                    '</div>';
                }).join('');
            }

            var replyHtml = renderReplies(topic.replies);
            var authorId   = topic.user_id || null;
            var authorName = topic.author  || 'Traveler';
            var topicMsgBtn = cfg.isLoggedIn ? messageBtn(authorId, authorName) : '';

            Swal.fire({
                title: topic.title,
                html:
                    '<div style="text-align:left;">' +
                        '<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">' +
                            '<span style="color:var(--text-muted);font-size:13px;">by <strong>' + authorName + '</strong> · ' + (topic.created_at || '') + '</span>' +
                            (topicMsgBtn ? '<div style="margin-left:auto;">' + topicMsgBtn + '</div>' : '') +
                        '</div>' +
                        '<p style="margin-bottom:16px;">' + (topic.body || '') + '</p>' +
                        '<div id="threadReplies" style="max-height:300px;overflow-y:auto;margin-bottom:16px;padding:10px;border:1px solid var(--border);border-radius:6px;">' + 
                            (replyHtml || '<p style="color:var(--text-muted);font-size:13px;text-align:center;">No replies yet. Be the first!</p>') + 
                        '</div>' +
                        '<textarea id="replyBody" placeholder="Write a reply…" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;font-family:inherit;font-size:14px;resize:vertical;min-height:70px;"></textarea>' +
                        '<button id="postReplyBtn" style="margin-top:10px;padding:10px 20px;background:var(--gold);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;width:100%;">' +
                            '<i class="fas fa-paper-plane"></i> Post Reply' +
                        '</button>' +
                    '</div>',
                showCancelButton: false,
                showConfirmButton: false,
                width: 640,
                didOpen: function() {
                    var postBtn = document.getElementById('postReplyBtn');
                    var textarea = document.getElementById('replyBody');
                    
                    if (postBtn) {
                        postBtn.addEventListener('click', function() {
                            if (!requireLogin('post replies')) return;
                            
                            var body = textarea.value.trim();
                            if (!body) {
                                showToast('Please write a reply');
                                return;
                            }
                            
                            postBtn.disabled = true;
                            postBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                            
                            apiFetch('/api/community/topics/' + id + '/replies', {
                                method: 'POST',
                                body:   JSON.stringify({ body: body }),
                            }).then(function(response) {
                                if (response && response.reply) {
                                    var newReply = response.reply;
                                    var rUserId = newReply.user_id || null;
                                    var rName   = newReply.author  || 'Traveler';
                                    var msgBtn  = cfg.isLoggedIn ? messageBtn(rUserId, rName) : '';
                                    
                                    var newReplyHtml = '<div class="reply-item" style="animation: fadeIn 0.3s ease-in;">' +
                                        '<div class="forum-avatar" style="width:34px;height:34px;font-size:12px;">' + initials(rName) + '</div>' +
                                        '<div class="reply-body">' +
                                            '<div class="reply-author"><strong>' + rName + '</strong> just now' +
                                                (msgBtn ? ' <span style="margin-left:8px;">' + msgBtn + '</span>' : '') +
                                            '</div>' +
                                            '<p>' + newReply.body + '</p>' +
                                        '</div>' +
                                    '</div>';
                                    
                                    var repliesContainer = document.getElementById('threadReplies');
                                    if (repliesContainer) {
                                        // Remove "No replies yet" message if it exists
                                        var emptyMsg = repliesContainer.querySelector('p[style*="text-align:center"]');
                                        if (emptyMsg) {
                                            repliesContainer.innerHTML = '';
                                        }
                                        
                                        repliesContainer.insertAdjacentHTML('beforeend', newReplyHtml);
                                        repliesContainer.scrollTop = repliesContainer.scrollHeight;
                                    }
                                    
                                    textarea.value = '';
                                    showToast('Reply posted!');
                                    loadTopics(); // Update reply count in topic list
                                }
                            }).catch(function() {
                                showToast('Failed to post reply');
                            }).finally(function() {
                                postBtn.disabled = false;
                                postBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Reply';
                            });
                        });
                    }
                    
                    // Allow Enter+Ctrl to submit
                    if (textarea) {
                        textarea.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                                e.preventDefault();
                                postBtn.click();
                            }
                        });
                    }
                }
            });
        }).catch(function () {});
    }

    function submitTopic() {
        var author = (document.getElementById('topicAuthor') || {}).value || '';
        var title  = (document.getElementById('topicTitle')  || {}).value || '';
        var tags   = (document.getElementById('topicTags')   || {}).value || '';
        var body   = (document.getElementById('topicBody')   || {}).value || '';
        if (!title.trim() || !body.trim()) { showToast('Please fill in the required fields'); return; }

        var btn = document.getElementById('submitTopicBtn');
        if (btn) btn.disabled = true;

        var tagArray = tags.split(',').map(function (t) { return t.trim(); }).filter(Boolean);

        apiFetch('/api/community/topics', {
            method: 'POST',
            body:   JSON.stringify({ author: author, title: title, tags: tagArray, body: body }),
        }).then(function () {
            closeModal('topicModal');
            showToast('Topic posted!');
            loadTopics();
            loadStats();
            ['topicAuthor', 'topicTitle', 'topicTags', 'topicBody'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.value = '';
            });
        }).catch(function () { showToast('Failed to post topic'); })
        .finally(function () { if (btn) btn.disabled = false; });
    }

    function submitGroup() {
        var org   = (document.getElementById('groupOrganizer') || {}).value || '';
        var name  = (document.getElementById('groupName')      || {}).value || '';
        var dest  = (document.getElementById('groupDest')      || {}).value || '';
        var date  = (document.getElementById('groupDate')      || {}).value || '';
        var spots = (document.getElementById('groupSpots')     || {}).value || '';
        if (!name.trim() || !dest.trim()) { showToast('Please fill in the required fields'); return; }

        var btn = document.getElementById('submitGroupBtn');
        if (btn) btn.disabled = true;

        apiFetch('/api/community/groups', {
            method: 'POST',
            body:   JSON.stringify({ organizer: org, name: name, destination: dest, date: date, spots_left: parseInt(spots) || 1 }),
        }).then(function () {
            closeModal('groupModal');
            showToast('Group trip created!');
            loadGroups();
            loadStats();
        }).catch(function () { showToast('Failed to create group'); })
        .finally(function () { if (btn) btn.disabled = false; });
    }

    function openInviteModal(encoded) {
        if (!requireLogin('send invites')) return;
        try { inviteTarget = JSON.parse(decodeURIComponent(encoded)); } catch (_) { return; }

        var avEl   = document.getElementById('inviteAvatar');
        var nameEl = document.getElementById('inviteName');
        var subEl  = document.getElementById('inviteSub');
        var msgEl  = document.getElementById('inviteMsg');

        if (avEl) {
            avEl.innerHTML = inviteTarget.avatar
                ? '<img src="' + inviteTarget.avatar + '">'
                : initials(inviteTarget.name);
        }
        if (nameEl) nameEl.textContent = inviteTarget.name;
        if (subEl)  subEl.textContent  = inviteTarget.sub;
        if (msgEl)  msgEl.value = 'Hey ' + inviteTarget.name.split(' ')[0] + '! I saw your profile on the Smart Booking community and would love to connect about travel plans. Would you be up for chatting?';

        document.getElementById('inviteModal').classList.add('open');
    }

    function sendInvite() {
        if (!inviteTarget || !inviteTarget.id) return;
        var body = (document.getElementById('inviteMsg') || {}).value || '';
        if (!body.trim()) { showToast('Please write a message'); return; }

        var btn = document.getElementById('submitInviteBtn');
        if (btn) btn.disabled = true;

        fetch('/api/messages', {
            method:      'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ receiver_id: inviteTarget.id, body: body }),
        }).then(function (r) {
            if (r.status === 401) { requireLogin('send messages'); throw new Error('unauthenticated'); }
            return r.json();
        }).then(function () {
            closeModal('inviteModal');
            window.location.href = '/chat/' + inviteTarget.id;
        }).catch(function (err) {
            if (err.message !== 'unauthenticated') showToast('Failed to send invite');
            if (btn) btn.disabled = false;
        });
    }

    function likeTopic(id, btn) {
        if (!requireLogin('like topics')) return;
        apiFetch('/api/community/topics/' + id + '/like', { method: 'POST' })
            .then(function(data) {
                if (btn) {
                    var countEl = btn.querySelector('.like-count');
                    if (countEl) countEl.textContent = data.likes || 0;
                    
                    if (data.liked) {
                        btn.classList.add('liked');
                        showToast('Liked!');
                    } else {
                        btn.classList.remove('liked');
                        showToast('Unliked');
                    }
                }
            })
            .catch(function() { showToast('Could not like topic'); });
    }

    function likeStory(id, btn) {
        if (!requireLogin('like stories')) return;
        apiFetch('/api/community/stories/' + id + '/like', { method: 'POST' })
            .then(function(data) {
                if (btn) {
                    var countEl = btn.querySelector('.like-count');
                    if (countEl) countEl.textContent = data.likes || 0;
                    
                    if (data.liked) {
                        btn.classList.add('liked');
                        showToast('Liked!');
                    } else {
                        btn.classList.remove('liked');
                        showToast('Unliked');
                    }
                }
            })
            .catch(function() { showToast('Could not like story'); });
    }

    function openStoryComments(storyId, storyTitle) {
        if (!requireLogin('comment on stories')) return;
        
        // Load existing comments
        apiFetch('/api/community/stories/' + storyId + '/comments')
            .then(function(data) {
                var comments = data.comments || [];
                var commentsHtml = comments.map(function(c) {
                    var deleteBtn = c.can_delete ? 
                        '<button class="comment-delete-btn" onclick="Community.deleteComment(' + c.id + ', ' + storyId + ')" title="Delete comment">' +
                            '<i class="fas fa-trash"></i>' +
                        '</button>' : '';
                    
                    return '<div class="reply-item" id="comment-' + c.id + '" style="margin-bottom:12px;">' +
                        '<div class="forum-avatar" style="width:34px;height:34px;font-size:12px;">' + initials(c.author) + '</div>' +
                        '<div class="reply-body">' +
                            '<div class="reply-author">' +
                                '<strong>' + c.author + '</strong> ' + c.created_at +
                                deleteBtn +
                            '</div>' +
                            '<p>' + c.body + '</p>' +
                        '</div>' +
                    '</div>';
                }).join('');

                Swal.fire({
                    title: storyTitle || 'Story Comments',
                    html:
                        '<div style="text-align:left;">' +
                        '<div id="storyCommentsList" style="max-height:300px;overflow-y:auto;margin-bottom:16px;padding:10px;border:1px solid var(--border);border-radius:6px;">' +
                        (commentsHtml || '<p style="color:var(--text-muted);font-size:13px;text-align:center;">No comments yet. Be the first!</p>') +
                        '</div>' +
                        '<textarea id="storyCommentBody" placeholder="Write a comment…" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;font-family:inherit;font-size:14px;resize:vertical;min-height:70px;"></textarea>' +
                        '<button id="postCommentBtn" style="margin-top:10px;padding:10px 20px;background:var(--gold);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;width:100%;">' +
                            '<i class="fas fa-paper-plane"></i> Post Comment' +
                        '</button>' +
                        '</div>',
                    showCancelButton: false,
                    showConfirmButton: false,
                    width: 560,
                    didOpen: function() {
                        var postBtn = document.getElementById('postCommentBtn');
                        var textarea = document.getElementById('storyCommentBody');
                        
                        if (postBtn) {
                            postBtn.addEventListener('click', function() {
                                var body = textarea.value.trim();
                                if (!body) {
                                    showToast('Please write a comment');
                                    return;
                                }
                                
                                postBtn.disabled = true;
                                postBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                                
                                apiFetch('/api/community/stories/' + storyId + '/comments', {
                                    method: 'POST',
                                    body:   JSON.stringify({ body: body }),
                                }).then(function(response) {
                                    if (response && response.comment) {
                                        var newComment = response.comment;
                                        var deleteBtn = newComment.can_delete ? 
                                            '<button class="comment-delete-btn" onclick="Community.deleteComment(' + newComment.id + ', ' + storyId + ')" title="Delete comment">' +
                                                '<i class="fas fa-trash"></i>' +
                                            '</button>' : '';
                                        
                                        var newCommentHtml = '<div class="reply-item" id="comment-' + newComment.id + '" style="margin-bottom:12px;animation: fadeIn 0.3s ease-in;">' +
                                            '<div class="forum-avatar" style="width:34px;height:34px;font-size:12px;">' + initials(newComment.author) + '</div>' +
                                            '<div class="reply-body">' +
                                                '<div class="reply-author">' +
                                                    '<strong>' + newComment.author + '</strong> just now' +
                                                    deleteBtn +
                                                '</div>' +
                                                '<p>' + newComment.body + '</p>' +
                                            '</div>' +
                                        '</div>';
                                        
                                        var commentsList = document.getElementById('storyCommentsList');
                                        if (commentsList) {
                                            var emptyMsg = commentsList.querySelector('p[style*="text-align:center"]');
                                            if (emptyMsg) {
                                                commentsList.innerHTML = '';
                                            }
                                            commentsList.insertAdjacentHTML('beforeend', newCommentHtml);
                                            commentsList.scrollTop = commentsList.scrollHeight;
                                        }
                                        
                                        textarea.value = '';
                                        showToast('Comment posted!');
                                        loadStories(); // Refresh to update comment count
                                    }
                                }).catch(function(err) {
                                    showToast('Failed to post comment');
                                }).finally(function() {
                                    postBtn.disabled = false;
                                    postBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Comment';
                                });
                            });
                        }
                        
                        if (textarea) {
                            textarea.addEventListener('keydown', function(e) {
                                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                                    e.preventDefault();
                                    postBtn.click();
                                }
                            });
                        }
                    }
                });
            })
            .catch(function() {
                showToast('Could not load comments');
            });
    }

    function deleteComment(commentId, storyId) {
        Swal.fire({
            title: 'Delete Comment?',
            text: 'Are you sure you want to delete this comment?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                apiFetch('/api/community/story-comments/' + commentId, {
                    method: 'DELETE'
                }).then(function(response) {
                    if (response.success) {
                        var commentEl = document.getElementById('comment-' + commentId);
                        if (commentEl) {
                            commentEl.style.animation = 'fadeOut 0.3s ease-out';
                            setTimeout(function() {
                                commentEl.remove();
                                
                                // Check if no comments left
                                var commentsList = document.getElementById('storyCommentsList');
                                if (commentsList && commentsList.children.length === 0) {
                                    commentsList.innerHTML = '<p style="color:var(--text-muted);font-size:13px;text-align:center;">No comments yet. Be the first!</p>';
                                }
                            }, 300);
                        }
                        showToast('Comment deleted');
                        loadStories(); // Refresh to update comment count
                    }
                }).catch(function() {
                    showToast('Failed to delete comment');
                });
            }
        });
    }

    function openTopicModal() {
        if (!requireLogin('post topics')) return;
        document.getElementById('topicModal').classList.add('open');
    }

    function openGroupModal() {
        if (!requireLogin('create groups')) return;
        document.getElementById('groupModal').classList.add('open');
    }

    function openStoryModal() {
        if (!requireLogin('create stories')) return;
        document.getElementById('storyModal').classList.add('open');
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
            loadStories();
            loadStats();
            // Clear form
            ['storyTitle', 'storyExcerpt', 'storyImageUrl', 'storyVideoUrl', 'storyThumbnailUrl', 'storyDuration'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.querySelector('input[name="mediaType"][value="image"]').checked = true;
            toggleMediaType('image');
        }).catch(function () {
            showToast('Failed to post story');
        }).finally(function () {
            if (btn) btn.disabled = false;
        });
    }

    function viewStory(storyId) {
        apiFetch('/api/community/stories/' + storyId).then(function (story) {
            var isVideo = story.media_type === 'video';
            var likedClass = story.is_liked ? 'liked' : '';
            
            var mediaHtml = isVideo
                ? '<video controls style="width:100%;max-height:500px;border-radius:8px;"><source src="' + story.video_url + '" type="video/mp4">Your browser does not support the video tag.</video>'
                : '<img src="' + story.image_url + '" style="width:100%;border-radius:8px;">';

            var content = '<div style="max-width:800px;margin:0 auto;padding:20px;">' +
                '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">' +
                    avatar({ name: story.author, avatar: story.author_avatar }, 44) +
                    '<div>' +
                        '<strong style="font-size:16px;">' + story.author + '</strong>' +
                        '<div style="color:var(--text-muted);font-size:13px;">' + story.created_at + '</div>' +
                    '</div>' +
                '</div>' +
                mediaHtml +
                '<div style="margin-top:16px;">' +
                    '<h2 style="margin-bottom:8px;">' + story.title + '</h2>' +
                    '<p style="color:var(--text-muted);margin-bottom:16px;">' + (story.excerpt || '') + '</p>' +
                    '<div style="display:flex;gap:16px;align-items:center;padding:12px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);">' +
                        '<button class="story-action-btn ' + likedClass + '" onclick="Community.likeStoryInView(' + story.id + ',this)" style="font-size:16px;">' +
                            '<i class="fas fa-heart"></i> <span class="like-count">' + (story.likes || 0) + '</span>' +
                        '</button>' +
                        '<button class="story-action-btn" onclick="Community.openStoryComments(' + story.id + ',\'' + story.title.replace(/'/g, "\\'") + '\')" style="font-size:16px;">' +
                            '<i class="fas fa-comment"></i> ' + (story.comments || 0) +
                        '</button>' +
                        (isVideo ? '<button class="story-action-btn" style="font-size:16px;"><i class="fas fa-eye"></i> ' + (story.views || 0) + '</button>' : '') +
                    '</div>' +
                '</div>' +
            '</div>';

            document.getElementById('storyViewContent').innerHTML = content;
            document.getElementById('storyViewModal').classList.add('open');
        }).catch(function () {
            showToast('Could not load story');
        });
    }

    function likeStoryInView(id, btn) {
        if (!requireLogin('like stories')) return;
        apiFetch('/api/community/stories/' + id + '/like', { method: 'POST' })
            .then(function(data) {
                if (btn) {
                    var countEl = btn.querySelector('.like-count');
                    if (countEl) countEl.textContent = data.likes || 0;
                    
                    if (data.liked) {
                        btn.classList.add('liked');
                        showToast('Liked!');
                    } else {
                        btn.classList.remove('liked');
                        showToast('Unliked');
                    }
                }
                loadStories(); // Refresh grid
            })
            .catch(function() { showToast('Could not like story'); });
    }

    function filterByMembers() {
        window.location.href = '/members';
    }

    function filterByStories() {
        document.getElementById('storiesGrid').scrollIntoView({ behavior: 'smooth' });
    }

    function filterByGroups() {
        document.getElementById('groupTrips').scrollIntoView({ behavior: 'smooth' });
    }

    function filterByTopics() {
        document.getElementById('forumTopics').scrollIntoView({ behavior: 'smooth' });
    }

    function viewProfile(userId) {
        if (!userId) return;
        window.location.href = '/users/' + userId + '/profile';
    }

    var currentFeedFilter = 'all';
    var allFeedItems = [];

    function switchTab(tab) {
        // Update tab buttons
        var tabs = document.querySelectorAll('.community-tab');
        tabs.forEach(function (t) {
            if (t.getAttribute('data-tab') === tab) {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });

        // Update tab content
        var contents = document.querySelectorAll('.community-tab-content');
        contents.forEach(function (c) {
            if (c.id === 'tab-' + tab) {
                c.classList.add('active');
            } else {
                c.classList.remove('active');
            }
        });

        // Load content based on tab
        if (tab === 'feed' && cfg.isLoggedIn) {
            loadFeed();
        } else if (tab === 'members') {
            loadMembersInline();
        }
    }

    function loadFeed() {
        apiFetch('/api/feed').then(function (data) {
            allFeedItems = data.stories || [];
            
            if (allFeedItems.length === 0) {
                document.getElementById('feedContent').style.display = 'none';
                document.getElementById('emptyFeed').style.display = 'block';
                return;
            }

            document.getElementById('feedContent').style.display = 'grid';
            document.getElementById('emptyFeed').style.display = 'none';
            renderFeed();
        }).catch(function () {
            showToast('Could not load feed');
        });
    }

    function renderFeed() {
        var el = document.getElementById('feedContent');
        if (!el) return;

        var items = currentFeedFilter === 'all' ? allFeedItems : 
                    allFeedItems.filter(function(item) {
                        return item.media_type === currentFeedFilter;
                    });

        if (items.length === 0) {
            el.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);">No posts to show for this filter.</div>';
            return;
        }

        el.innerHTML = items.map(function (item) {
            var isVideo = item.media_type === 'video';
            var mediaUrl = isVideo ? (item.thumbnail_url || item.video_url) : item.image_url;
            var likedClass = item.is_liked ? 'liked' : '';
            var isOwnPost = item.user_id === cfg.authUserId;

            var videoOverlay = isVideo ? '<div class="video-overlay"><i class="fas fa-play-circle"></i>' + 
                (item.duration ? '<span class="video-duration">' + formatDuration(item.duration) + '</span>' : '') + 
                '</div>' : '';

            return '<div class="feed-item-card" onclick="Community.viewStory(' + item.id + ')">' +
                '<div class="feed-item-media" style="background-image:url(\'' + mediaUrl + '\');">' +
                    videoOverlay +
                '</div>' +
                '<div class="feed-item-info">' +
                    '<div class="feed-item-author" onclick="event.stopPropagation();Community.viewProfile(' + item.user_id + ')">' +
                        avatar({ name: item.author, avatar: item.author_avatar }, 32) +
                        '<strong>' + item.author + '</strong>' +
                    '</div>' +
                    '<h4>' + item.title + '</h4>' +
                    '<p>' + (item.excerpt || '').substring(0, 100) + '...</p>' +
                    '<div class="feed-item-actions" onclick="event.stopPropagation();">' +
                        '<button class="story-action-btn ' + likedClass + '" onclick="Community.likeStory(' + item.id + ',this)">' +
                            '<i class="fas fa-heart"></i> ' + (item.likes || 0) +
                        '</button>' +
                        '<button class="story-action-btn">' +
                            '<i class="fas fa-comment"></i> ' + (item.comments || 0) +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function filterFeed(filter) {
        currentFeedFilter = filter;
        
        // Update active button
        var buttons = document.querySelectorAll('.filter-btn-inline');
        buttons.forEach(function (btn) {
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        renderFeed();
    }

    function loadMembersInline() {
        apiFetch('/api/community/members').then(function (data) {
            var el = document.getElementById('membersGridInline');
            if (!el) return;
            
            var members = (data.members || []).filter(function(m) {
                return m.id !== cfg.authUserId; // Don't show yourself
            });

            el.innerHTML = members.map(function (member) {
                var avatarHtml = member.avatar
                    ? '<img src="' + member.avatar + '" alt="' + member.name + '">'
                    : '<div class="member-avatar-initials">' + initials(member.name) + '</div>';

                return '<div class="member-card-inline" onclick="Community.viewProfile(' + member.id + ')">' +
                    '<div class="member-avatar-inline">' + avatarHtml + '</div>' +
                    '<h4>' + member.name + '</h4>' +
                    '<p>' + (member.location || 'Traveler') + '</p>' +
                    '<div class="member-stats-inline">' +
                        '<span>' + (member.posts || 0) + ' posts</span>' +
                        '<span>' + (member.followers || 0) + ' followers</span>' +
                    '</div>' +
                '</div>';
            }).join('');
        }).catch(function () {});
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('open');
    }

    document.addEventListener('click', function (e) {
        ['topicModal', 'groupModal', 'inviteModal', 'storyModal', 'storyViewModal'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && e.target === el) el.classList.remove('open');
        });
    });

    function initPusher() {
        if (!cfg.pusherKey) return;
        try {
            var pusher  = new Pusher(cfg.pusherKey, { cluster: cfg.pusherCluster });
            var channel = pusher.subscribe('community');
            channel.bind('new-topic', function () { loadTopics(); loadStats(); });
            channel.bind('new-reply', function () { loadTopics(); });
            channel.bind('new-group', function () { loadGroups(); loadStats(); });
        } catch (_) {}
    }

    function init() {
        loadStats();
        loadTopics();
        loadGroups();
        loadTags();
        loadStories();
        loadTravelers();
        initPusher();
        
        // Load feed if user is logged in and feed tab is active
        if (cfg.isLoggedIn) {
            var activeTab = document.querySelector('.community-tab.active');
            if (activeTab && activeTab.getAttribute('data-tab') === 'feed') {
                loadFeed();
            }
        }
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);

    window.Community = {
        openTopicModal:  openTopicModal,
        openGroupModal:  openGroupModal,
        openStoryModal:  openStoryModal,
        openInviteModal: openInviteModal,
        closeModal:      closeModal,
        submitTopic:     submitTopic,
        submitGroup:     submitGroup,
        submitStory:     submitStory,
        sendInvite:      sendInvite,
        openTopic:       openTopic,
        startChat:       startChat,
        likeTopic:       likeTopic,
        likeStory:       likeStory,
        likeStoryInView: likeStoryInView,
        openStoryComments: openStoryComments,
        deleteComment:   deleteComment,
        joinGroup:       joinGroup,
        viewStory:       viewStory,
        viewProfile:     viewProfile,
        toggleMediaType: toggleMediaType,
        filterByMembers: filterByMembers,
        filterByStories: filterByStories,
        filterByGroups:  filterByGroups,
        filterByTopics:  filterByTopics,
        switchTab:       switchTab,
        filterFeed:      filterFeed,
    };

}());