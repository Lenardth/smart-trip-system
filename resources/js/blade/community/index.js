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
                var invBtn     = cfg.isLoggedIn ? inviteBtn(authorId, authorName, null, 'Forum member') : '';

                return '<div class="forum-topic">' +
                    '<div class="forum-avatar">' + initials(authorName) + '</div>' +
                    '<div class="ft-body">' +
                        '<h4 onclick="Community.openTopic(' + t.id + ')">' + t.title + '</h4>' +
                        '<div class="ft-meta">' +
                            '<span>by <strong>' + authorName + '</strong></span> · ' + (t.created_at || '') +
                        '</div>' +
                        '<div style="margin-top:6px;">' + tags + '</div>' +
                        (msgBtn || invBtn
                            ? '<div class="ft-actions" style="display:flex;gap:8px;margin-top:8px;">' + msgBtn + invBtn + '</div>'
                            : '') +
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
                var full     = g.spots_taken >= g.spots_total;
                var badgeCls = full ? 'gt-badge full' : 'gt-badge';
                var badgeTxt = full ? 'Full' : (g.spots_available || (g.spots_total - (g.spots_taken || 0))) + ' spots left';
                var orgId    = g.user_id || null;
                var orgName  = g.organizer || 'Organizer';
                var msgBtn   = cfg.isLoggedIn ? messageBtn(orgId, orgName) : '';

                return '<div class="group-trip">' +
                    '<div class="gt-icon"><i class="fas fa-map-marked-alt"></i></div>' +
                    '<div class="gt-info">' +
                        '<h4>' + g.name + '</h4>' +
                        '<p>' + (g.destination || '') + (g.date ? ' · ' + g.date : '') + '</p>' +
                        (msgBtn ? '<div style="margin-top:6px;">' + msgBtn + '</div>' : '') +
                    '</div>' +
                    '<span class="' + badgeCls + '">' + badgeTxt + '</span>' +
                '</div>';
            }).join('');
        }).catch(function () {});
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
                var msgBtn     = cfg.isLoggedIn ? messageBtn(authorId, authorName) : '';

                return '<div class="story-card">' +
                    '<div class="story-img" style="background-image:url(\'' + (s.image_url || s.image || '') + '\');"></div>' +
                    '<div class="story-body">' +
                        '<div class="story-author">' +
                            avatar({ name: authorName, avatar: s.author_avatar }, 34) +
                            '<div class="sa-info">' +
                                '<strong>' + authorName + '</strong>' +
                                (s.created_at || '') +
                            '</div>' +
                            (msgBtn ? '<div style="margin-left:auto;">' + msgBtn + '</div>' : '') +
                        '</div>' +
                        '<h4>' + s.title + '</h4>' +
                        '<p>' + (s.excerpt || '') + '</p>' +
                        '<div class="story-footer">' +
                            '<span><i class="fas fa-heart" style="color:var(--gold);"></i> ' + (s.likes || 0) + '</span>' +
                            '<span><i class="fas fa-comment"></i> ' + (s.comments || 0) + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            }).join('');
        }).catch(function () {});
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
                var msgBtn = cfg.isLoggedIn ? messageBtn(userId, t.name) : '';
                var invBtn = cfg.isLoggedIn ? inviteBtn(userId, t.name, t.avatar, t.location || 'Traveler') : '';
                var av     = t.avatar
                    ? '<img src="' + t.avatar + '" style="width:100%;height:100%;object-fit:cover;">'
                    : initials(t.name);

                return '<div class="traveler-card">' +
                    '<div class="traveler-avatar">' + av + '</div>' +
                    '<h4>' + t.name + '</h4>' +
                    '<p class="tc-sub">' + (t.location || t.bio || 'Traveler') + '</p>' +
                    '<div class="tc-stats">' +
                        '<div class="tc-stat"><div class="ts-num">' + (t.trips || 0) + '</div><div class="ts-label">Trips</div></div>' +
                        '<div class="tc-stat"><div class="ts-num">' + (t.countries || 0) + '</div><div class="ts-label">Countries</div></div>' +
                    '</div>' +
                    (t.badge ? '<div class="tc-badge">' + t.badge + '</div>' : '') +
                    '<div class="tc-actions">' + msgBtn + invBtn + '</div>' +
                '</div>';
            }).join('');
        }).catch(function () {});
    }

    function openTopic(id) {
        apiFetch('/api/community/topics/' + id).then(function (data) {
            var topic     = data.topic || data;
            var replyHtml = (topic.replies || []).map(function (r) {
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
                        '<div id="threadReplies">' + (replyHtml || '<p style="color:var(--text-muted);font-size:13px;">No replies yet.</p>') + '</div>' +
                        '<hr style="margin:16px 0;border-color:var(--border);">' +
                        '<textarea id="replyBody" placeholder="Write a reply…" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;font-family:inherit;font-size:14px;resize:vertical;min-height:70px;"></textarea>' +
                    '</div>',
                showCancelButton: true,
                confirmButtonColor: '#c9a96e',
                cancelButtonColor: '#6b5b4f',
                confirmButtonText: '<i class="fas fa-paper-plane"></i> Post Reply',
                cancelButtonText: 'Close',
                width: 640,
                preConfirm: function () {
                    if (!requireLogin('post replies')) return false;
                    var body = document.getElementById('replyBody').value.trim();
                    if (!body) { Swal.showValidationMessage('Please write a reply'); return false; }
                    return apiFetch('/api/community/topics/' + id + '/replies', {
                        method: 'POST',
                        body:   JSON.stringify({ body: body }),
                    });
                },
            }).then(function (r) {
                if (r.isConfirmed && r.value) {
                    showToast('Reply posted!');
                    loadTopics();
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

    function openTopicModal() {
        if (!requireLogin('post topics')) return;
        document.getElementById('topicModal').classList.add('open');
    }

    function openGroupModal() {
        if (!requireLogin('create groups')) return;
        document.getElementById('groupModal').classList.add('open');
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('open');
    }

    document.addEventListener('click', function (e) {
        ['topicModal', 'groupModal', 'inviteModal'].forEach(function (id) {
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
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);

    window.Community = {
        openTopicModal:  openTopicModal,
        openGroupModal:  openGroupModal,
        openInviteModal: openInviteModal,
        closeModal:      closeModal,
        submitTopic:     submitTopic,
        submitGroup:     submitGroup,
        sendInvite:      sendInvite,
        openTopic:       openTopic,
        startChat:       startChat,
    };

}());
