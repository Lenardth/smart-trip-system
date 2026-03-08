const Community = (() => {

    const CSRF = window.__COMMUNITY__.csrfToken;
    const cfg  = window.__COMMUNITY__;

    const AVATAR_COLORS = ['#3b1f2b','#4d2a3a','#5a3040','#3b2535','#4a2838','#2f1a24'];

    /* ── Utilities ── */

    function initials(name) {
        if (!name) return '??';
        return name.trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('');
    }

    function avatarColor(name) {
        let n = 0;
        for (let i = 0; i < (name || '').length; i++) n += name.charCodeAt(i);
        return AVATAR_COLORS[n % AVATAR_COLORS.length];
    }

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)    return 'just now';
        if (diff < 3600)  return Math.floor(diff / 60) + ' min ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
        return Math.floor(diff / 86400) + ' day' + (Math.floor(diff / 86400) > 1 ? 's' : '') + ' ago';
    }

    function animateCount(el, target) {
        const current = parseInt(el.textContent.replace(/,/g,'')) || 0;
        if (current === target) return;
        const dur = 800, startTime = performance.now();
        const step = now => {
            const t    = Math.min((now - startTime) / dur, 1);
            const ease = t < .5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
            el.textContent = Math.round(current + (target - current) * ease).toLocaleString();
            if (t < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }

    function bumpStat(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        const prev = parseInt(el.textContent.replace(/,/g,'')) || 0;
        if (prev === value) return;
        animateCount(el, value);
        el.classList.add('bump');
        setTimeout(() => el.classList.remove('bump'), 600);
    }

    function showToast(msg, icon = 'fa-check-circle') {
        const t = document.getElementById('toast');
        t.querySelector('i').className = `fas ${icon}`;
        document.getElementById('toastMsg').textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3500);
    }

    function apiFetch(url, options = {}) {
        return fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            ...options
        }).then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        });
    }

    /* ── Modal helpers ── */

    function openModal(id) {
        document.getElementById(id).classList.add('open');
        document.addEventListener('keydown', escListener);
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
        document.removeEventListener('keydown', escListener);
    }

    function escListener(e) {
        if (e.key === 'Escape')
            document.querySelectorAll('.modal-overlay.open')
                .forEach(m => m.classList.remove('open'));
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    /* ── Stats ── */

    function loadStats() {
        apiFetch('/api/community/stats')
            .then(data => {
                bumpStat('stat-members', data.members);
                bumpStat('stat-stories', data.stories);
                bumpStat('stat-groups',  data.groups);
                bumpStat('stat-topics',  data.topics);
            })
            .catch(() => {
                ['stat-members','stat-stories','stat-groups','stat-topics'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.querySelector('.skeleton')) el.textContent = '—';
                });
            });
    }

    /* ── Forum ── */

    let forumTopics     = [];
    let lastTopicCount  = 0;

    function renderTopics(topics, prepend = false) {
        const container = document.getElementById('forumTopics');
        if (!topics.length) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-comments"></i><p>No topics yet. Be the first!</p></div>';
            return;
        }
        if (prepend && forumTopics.length) {
            const el = buildTopicEl(topics[0], true);
            container.insertBefore(el, container.firstChild);
            setTimeout(() => el.classList.remove('is-new'), 3000);
            forumTopics = [topics[0], ...forumTopics];
        } else {
            container.innerHTML = '';
            topics.forEach(t => container.appendChild(buildTopicEl(t)));
            forumTopics = topics;
        }
        lastTopicCount = forumTopics.length;
    }

    function buildTopicEl(t, isNew = false) {
        const div = document.createElement('div');
        div.className = 'forum-topic' + (isNew ? ' is-new' : '');
        div.dataset.topicId = t.id;
        const tags  = (t.tags || []).map(tag => `<span class="ft-tag">${tag}</span>`).join('');
        const color = avatarColor(t.author);
        div.innerHTML = `
            <div class="forum-avatar" style="background:${color}">${initials(t.author)}</div>
            <div class="ft-body">
                <div>${tags}</div>
                <h4 class="topic-title-link">${t.title}</h4>
                <div class="ft-meta">Posted by <strong>${t.author}</strong> · ${timeAgo(t.created_at)}</div>
            </div>
            <div class="ft-stats">
                <div class="fs-num reply-count-${t.id}">${t.replies ?? 0}</div>
                <div class="fs-label">Replies</div>
                <button class="reply-btn" data-id="${t.id}" title="View & reply">
                    <i class="fas fa-reply"></i> Reply
                </button>
            </div>`;
        div.querySelector('.topic-title-link').addEventListener('click', () => openTopicThread(t.id));
        div.querySelector('.reply-btn').addEventListener('click', () => openTopicThread(t.id));
        return div;
    }

    function loadTopics(silent = false) {
        apiFetch('/api/community/topics')
            .then(data => {
                const topics = data.data ?? data;
                // Only re-render if something changed
                if (!silent || topics.length !== lastTopicCount) {
                    renderTopics(topics);
                }
            })
            .catch(() => {
                if (!silent) {
                    document.getElementById('forumTopics').innerHTML =
                        '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not load topics.</p></div>';
                }
            });
    }

    /* ── Topic Thread Modal (replies) ── */

    function openTopicThread(topicId) {
        let modal = document.getElementById('threadModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'threadModal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal" style="max-width:680px;max-height:90vh;display:flex;flex-direction:column;">
                    <div class="modal-header" style="flex-shrink:0;">
                        <h2 id="threadTitle" style="font-size:16px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:500px;"></h2>
                        <button class="modal-close" id="threadClose">&#x2715;</button>
                    </div>
                    <div style="flex:1;overflow-y:auto;display:flex;flex-direction:column;">
                        <div id="threadMeta" style="padding:16px 28px 14px;border-bottom:1px solid var(--border);flex-shrink:0;"></div>
                        <div id="threadReplies" style="padding:8px 28px;flex:1;overflow-y:auto;min-height:80px;"></div>
                        <div style="padding:20px 28px;border-top:1px solid var(--border);background:var(--cream);flex-shrink:0;">
                            <p style="font-size:13px;font-weight:bold;color:var(--deep);margin:0 0 10px;">
                                <i class="fas fa-reply" style="color:var(--gold);margin-right:6px;"></i>Post a Reply
                            </p>
                            <div class="form-group" style="margin-bottom:10px;">
                                <input type="text" id="replyAuthor" placeholder="Your name" autocomplete="name">
                            </div>
                            <div class="form-group" style="margin-bottom:12px;">
                                <textarea id="replyBody" placeholder="Write your reply..." style="min-height:70px;"></textarea>
                            </div>
                            <div style="display:flex;justify-content:flex-end;gap:10px;">
                                <button class="secondary-button" id="threadCancelBtn">Cancel</button>
                                <button class="primary-button" id="submitReplyBtn">
                                    <i class="fas fa-paper-plane"></i> Post Reply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
            modal.addEventListener('click', e => { if (e.target === modal) closeModal('threadModal'); });
            document.getElementById('threadClose').addEventListener('click', () => closeModal('threadModal'));
            document.getElementById('threadCancelBtn').addEventListener('click', () => closeModal('threadModal'));
        }

        modal.dataset.topicId = topicId;
        document.getElementById('replyBody').value = '';
        document.getElementById('submitReplyBtn').onclick = () => submitReply(topicId);
        document.getElementById('threadTitle').textContent = 'Loading…';
        document.getElementById('threadMeta').innerHTML = '';
        document.getElementById('threadReplies').innerHTML =
            '<div style="padding:20px;text-align:center;"><i class="fas fa-spinner fa-spin" style="color:var(--gold);font-size:22px;"></i></div>';

        openModal('threadModal');

        apiFetch(`/api/community/topics/${topicId}`)
            .then(data => {
                const t    = data.topic;
                const tags = (t.tags || []).map(tag => `<span class="ft-tag">${tag}</span>`).join('');
                document.getElementById('threadTitle').innerHTML =
                    `<i class="fas fa-comments" style="color:var(--gold);margin-right:8px;"></i>${t.title}`;
                document.getElementById('threadMeta').innerHTML = `
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                        <div class="forum-avatar" style="background:${avatarColor(t.author)};width:36px;height:36px;font-size:13px;flex-shrink:0;">${initials(t.author)}</div>
                        <div>
                            <div style="font-weight:bold;color:var(--deep);font-size:13px;">${t.author}</div>
                            <div style="color:var(--text-muted);font-size:11px;">${timeAgo(t.created_at)}</div>
                        </div>
                    </div>
                    ${t.body ? `<p style="color:var(--text-muted);font-size:13px;line-height:1.6;margin:0 0 8px;text-align:left;">${t.body}</p>` : ''}
                    <div style="text-align:left;">${tags}</div>`;
                renderReplies(data.replies || []);
            })
            .catch(() => {
                document.getElementById('threadReplies').innerHTML =
                    '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not load topic. Please try again.</p></div>';
            });
    }

    function renderReplies(replies) {
        const container = document.getElementById('threadReplies');
        if (!replies.length) {
            container.innerHTML = `
                <div class="empty-state" style="padding:24px 0;">
                    <i class="fas fa-comment-slash"></i>
                    <p>No replies yet — be the first to respond!</p>
                </div>`;
            return;
        }
        container.innerHTML = replies.map(r => `
            <div class="reply-item">
                <div class="forum-avatar" style="background:${avatarColor(r.author)};width:34px;height:34px;font-size:12px;flex-shrink:0;">${initials(r.author)}</div>
                <div class="reply-body">
                    <div class="reply-author"><strong>${r.author}</strong><span>${timeAgo(r.created_at)}</span></div>
                    <p>${r.body}</p>
                </div>
            </div>`).join('');
        container.scrollTop = container.scrollHeight;
    }

    function submitReply(topicId) {
        const btn    = document.getElementById('submitReplyBtn');
        const author = document.getElementById('replyAuthor').value.trim();
        const body   = document.getElementById('replyBody').value.trim();
        if (!author) { showToast('Please enter your name.', 'fa-exclamation-circle'); return; }
        if (!body)   { showToast('Please write a reply.', 'fa-exclamation-circle'); return; }
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting…';
        apiFetch(`/api/community/topics/${topicId}/replies`, {
            method: 'POST',
            body: JSON.stringify({ author, body }),
        })
        .then(data => {
            document.getElementById('replyBody').value = '';
            showToast('Reply posted!');
            const container = document.getElementById('threadReplies');
            const empty = container.querySelector('.empty-state');
            if (empty) container.innerHTML = '';
            const r  = data.reply;
            const el = document.createElement('div');
            el.className = 'reply-item is-new';
            el.innerHTML = `
                <div class="forum-avatar" style="background:${avatarColor(r.author)};width:34px;height:34px;font-size:12px;flex-shrink:0;">${initials(r.author)}</div>
                <div class="reply-body">
                    <div class="reply-author"><strong>${r.author}</strong><span>just now</span></div>
                    <p>${r.body}</p>
                </div>`;
            container.appendChild(el);
            container.scrollTop = container.scrollHeight;
            const countEl = document.querySelector(`.reply-count-${topicId}`);
            if (countEl) countEl.textContent = data.reply_count;
        })
        .catch(() => showToast('Failed to post reply. Please try again.', 'fa-exclamation-circle'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Reply';
        });
    }

    /* ── New Topic ── */

    function openTopicModal() { openModal('topicModal'); }

    function submitTopic() {
        const btn    = document.getElementById('submitTopicBtn');
        const author = document.getElementById('topicAuthor').value.trim();
        const title  = document.getElementById('topicTitle').value.trim();
        const tags   = document.getElementById('topicTags').value.trim();
        const body   = document.getElementById('topicBody').value.trim();
        if (!author || !title) {
            showToast('Please fill in your name and topic title.', 'fa-exclamation-circle');
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting…';
        apiFetch('/api/community/topics', {
            method: 'POST',
            body: JSON.stringify({
                author, title,
                tags: tags ? tags.split(',').map(t => t.trim()).filter(Boolean) : [],
                body,
            })
        })
        .then(() => {
            closeModal('topicModal');
            ['topicAuthor','topicTitle','topicTags','topicBody'].forEach(id => {
                document.getElementById(id).value = '';
            });
            showToast('Topic posted!');
            loadTopics();
            loadStats();
            loadTags();
        })
        .catch(() => showToast('Failed to post topic.', 'fa-exclamation-circle'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Topic';
        });
    }

    /* ── Group Trips ── */

    let lastGroupHash = '';

    function renderGroups(groups) {
        const container = document.getElementById('groupTrips');
        const hash = JSON.stringify(groups.map(g => g.id + g.spots_left + g.status));
        if (hash === lastGroupHash) return; // no change
        lastGroupHash = hash;

        if (!groups.length) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No group trips yet.</p></div>';
            return;
        }
        const icons = ['fa-map-marker-alt','fa-mountain','fa-umbrella-beach','fa-plane','fa-globe','fa-ship'];
        container.innerHTML = groups.map((g, i) => {
            const full     = g.spots_left <= 0 || g.status === 'full';
            const badgeCls = full ? 'gt-badge full' : 'gt-badge';
            const badgeTxt = full ? 'Full' : `${g.spots_left} left`;
            return `
                <div class="group-trip">
                    <div class="gt-icon"><i class="fas ${icons[i % icons.length]}"></i></div>
                    <div class="gt-info">
                        <h4>${g.name}</h4>
                        <p><i class="fas fa-map-marker-alt" style="font-size:10px;"></i> ${g.destination} · ${g.date}</p>
                    </div>
                    <span class="${badgeCls}">${badgeTxt}</span>
                </div>`;
        }).join('');
    }

    function loadGroups(silent = false) {
        apiFetch('/api/community/groups')
            .then(data => renderGroups(data.data ?? data))
            .catch(() => {
                if (!silent) {
                    document.getElementById('groupTrips').innerHTML =
                        '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not load groups.</p></div>';
                }
            });
    }

    function openGroupModal() { openModal('groupModal'); }

    function submitGroup() {
        const btn       = document.getElementById('submitGroupBtn');
        const organizer = document.getElementById('groupOrganizer').value.trim();
        const name      = document.getElementById('groupName').value.trim();
        const dest      = document.getElementById('groupDest').value.trim();
        const date      = document.getElementById('groupDate').value.trim();
        const spots     = parseInt(document.getElementById('groupSpots').value) || 0;
        if (!organizer || !name || !dest || !date) {
            showToast('Please fill in all required fields.', 'fa-exclamation-circle'); return;
        }
        if (spots < 1) { showToast('Spots must be at least 1.', 'fa-exclamation-circle'); return; }
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating…';
        apiFetch('/api/community/groups', {
            method: 'POST',
            body: JSON.stringify({ organizer, name, destination: dest, date, spots_left: spots })
        })
        .then(() => {
            closeModal('groupModal');
            ['groupOrganizer','groupName','groupDest','groupDate','groupSpots'].forEach(id => {
                document.getElementById(id).value = '';
            });
            showToast('Group trip created!');
            loadGroups();
            loadStats();
        })
        .catch(() => showToast('Failed to create group.', 'fa-exclamation-circle'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Create Group';
        });
    }

    /* ── Tags ── */

    function renderTags(tags) {
        const container = document.getElementById('trendingTags');
        if (!tags.length) {
            container.innerHTML = '<p style="color:var(--text-muted);font-size:13px;">No tags yet.</p>';
            return;
        }
        container.innerHTML = tags.map(tag =>
            `<button class="tag-item" onclick="Community.filterByTag('${tag.name}')">#${tag.name}</button>`
        ).join('');
    }

    function loadTags(silent = false) {
        apiFetch('/api/community/tags')
            .then(renderTags)
            .catch(() => { if (!silent) document.getElementById('trendingTags').innerHTML = ''; });
    }

    function filterByTag(tag) {
        showToast(`Filtering by #${tag}`, 'fa-tag');
    }

    /* ── Stories ── */

    let lastStoryHash = '';

    function renderStories(stories) {
        const grid = document.getElementById('storiesGrid');
        const hash = JSON.stringify(stories.map(s => s.id + s.likes));
        if (hash === lastStoryHash) return;
        lastStoryHash = hash;

        if (!stories.length) {
            grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-book-open"></i><p>No stories yet.</p></div>';
            return;
        }
        grid.innerHTML = stories.map(s => {
            const imgStyle = s.image_url ? `background-image:url('${s.image_url}')` : 'background:var(--border)';
            return `
                <div class="story-card">
                    <div class="story-img" style="${imgStyle}"></div>
                    <div class="story-body">
                        <div class="story-author">
                            <div class="sa-avatar" style="background:${avatarColor(s.author)}">${initials(s.author)}</div>
                            <div class="sa-info">
                                <strong>${s.author}</strong>
                                ${s.published_at ? new Date(s.published_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : ''}
                            </div>
                        </div>
                        <h4>${s.title}</h4>
                        <p>${s.excerpt ?? ''}</p>
                        <div class="story-footer">
                            <span><i class="fas fa-heart" style="color:#e57373;"></i> ${(s.likes ?? 0).toLocaleString()}</span>
                            <span><i class="fas fa-comment" style="color:var(--gold);"></i> ${(s.comments ?? 0).toLocaleString()}</span>
                        </div>
                    </div>
                </div>`;
        }).join('');
    }

    function loadStories(silent = false) {
        apiFetch('/api/community/stories')
            .then(data => renderStories(data.data ?? data))
            .catch(() => {
                if (!silent) {
                    document.getElementById('storiesGrid').innerHTML =
                        '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-exclamation-circle"></i><p>Could not load stories.</p></div>';
                }
            });
    }

    /* ── Top Travelers ── */

    function renderTravelers(travelers) {
        const grid = document.getElementById('travelersGrid');
        if (!travelers.length) {
            grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-user-friends"></i><p>No travelers yet.</p></div>';
            return;
        }
        grid.innerHTML = travelers.map(t => `
            <div class="traveler-card">
                <div class="traveler-avatar" style="background:${avatarColor(t.name)}">${initials(t.name)}</div>
                <h4>${t.name}</h4>
                <div class="tc-sub">${t.bio ?? 'Travel enthusiast'}</div>
                <div class="tc-stats">
                    <div class="tc-stat"><div class="ts-num">${t.trips ?? 0}</div><div class="ts-label">Trips</div></div>
                    <div class="tc-stat"><div class="ts-num">${t.countries ?? 0}</div><div class="ts-label">Countries</div></div>
                    <div class="tc-stat"><div class="ts-num">${t.posts ?? 0}</div><div class="ts-label">Posts</div></div>
                </div>
                ${t.badge ? `<div class="tc-badge">${t.badge}</div>` : ''}
            </div>`).join('');
    }

    function loadTravelers() {
        apiFetch('/api/community/travelers')
            .then(data => renderTravelers(data.data ?? data))
            .catch(() => {
                document.getElementById('travelersGrid').innerHTML =
                    '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-exclamation-circle"></i><p>Could not load travelers.</p></div>';
            });
    }

    /* ── Polling fallback (when Pusher not configured) ── */

    function startPolling() {
        // Poll every 15 seconds for new topics, groups, stories, stats
        setInterval(() => {
            loadStats();
            loadTopics(true);
            loadGroups(true);
            loadStories(true);
            loadTags(true);
        }, 15000);
    }

    /* ── Real-time via Pusher ── */

    function initPusher() {
        if (!cfg.pusherKey || cfg.pusherKey === '') {
            console.info('Pusher not configured — using polling fallback (15s interval).');
            startPolling();
            return;
        }

        Pusher.logToConsole = false;
        const pusher  = new Pusher(cfg.pusherKey, { cluster: cfg.pusherCluster });
        const channel = pusher.subscribe('community');

        channel.bind('topic.created', data => {
            renderTopics([data.topic], true);
            loadStats();
            loadTags(true);
            showToast(`New topic: "${data.topic.title}"`, 'fa-comments');
        });

        channel.bind('group.created', data => {
            loadGroups();
            loadStats();
            showToast(`New group trip: "${data.group.name}"`, 'fa-users');
        });

        channel.bind('story.created', data => {
            loadStories();
            loadStats();
            showToast(`New story: "${data.story.title}"`, 'fa-book-open');
        });

        channel.bind('stats.updated', data => {
            if (data.members !== undefined) bumpStat('stat-members', data.members);
            if (data.stories !== undefined) bumpStat('stat-stories', data.stories);
            if (data.groups  !== undefined) bumpStat('stat-groups',  data.groups);
            if (data.topics  !== undefined) bumpStat('stat-topics',  data.topics);
        });

        pusher.connection.bind('connected',    () => {
            console.info('Pusher connected — real-time active.');
        });
        pusher.connection.bind('disconnected', () => {
            console.warn('Pusher disconnected — falling back to polling.');
            startPolling();
        });
        pusher.connection.bind('failed', () => {
            console.warn('Pusher failed — falling back to polling.');
            startPolling();
        });
    }

    /* ── Boot ── */

    function init() {
        loadStats();
        loadTopics();
        loadGroups();
        loadTags();
        loadStories();
        loadTravelers();
        initPusher();
    }

    document.addEventListener('DOMContentLoaded', init);

    return {
        openTopicModal,
        openGroupModal,
        closeModal,
        submitTopic,
        submitGroup,
        filterByTag,
        openTopicThread,
    };

})();
