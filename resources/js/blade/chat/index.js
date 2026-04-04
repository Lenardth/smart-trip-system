import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const Chat = {
    threadUserId: null,
    threadUserName: '',
    threadUserAvatar: '',
    messages: [],
    pollTimer: null,
    ME: null,
    MY_NAME: '',
    MY_AVATAR: '',
    cfg: {},
};

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function esc(v) {
    if (!v) return '';
    return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function initials(name) {
    return (name || 'U').split(' ').map(p => p[0]).join('').toUpperCase().substring(0, 2);
}

function avatarHtml(name, avatarUrl, size = 28) {
    const s = `width:${size}px;height:${size}px;`;
    if (avatarUrl && !avatarUrl.includes('default-avatar')) {
        return `<img src="${esc(avatarUrl)}" style="${s}border-radius:50%;object-fit:cover;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">` +
               `<span style="${s}display:none;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--deep-alt));color:#fff;font-size:${Math.round(size*0.38)}px;font-weight:700;align-items:center;justify-content:center;">${initials(name)}</span>`;
    }
    return `<span style="${s}display:flex;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--deep-alt));color:#fff;font-size:${Math.round(size*0.38)}px;font-weight:700;align-items:center;justify-content:center;">${initials(name)}</span>`;
}

async function apiFetch(url, opts = {}) {
    const res = await fetch(url, {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        credentials: 'same-origin',
        ...opts,
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

function ensureEcho() {
    if (window.Echo) return window.Echo;
    const key = Chat.cfg.pusherKey;
    if (!Chat.ME || !key) return null;
    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key,
            cluster: Chat.cfg.pusherCluster || 'mt1',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': csrf() } },
        });
        return window.Echo;
    } catch (_) { return null; }
}

async function loadConversations() {
    const list = document.getElementById('pageConvList');
    if (!list) return;
    try {
        const convs = await apiFetch('/api/conversations');
        if (!convs.length) {
            list.innerHTML = '<div class="conv-empty"><i class="fas fa-comment-slash"></i><p>No conversations yet.<br>Search for someone to start chatting.</p></div>';
            return;
        }
        list.innerHTML = convs.map(c => {
            const u = c.user;
            const unread = c.unread_count > 0 ? `<span class="conv-badge">${c.unread_count}</span>` : '';
            const last = esc(c.last_message?.body || 'No messages yet');
            const time = c.last_message?.created_at ? new Date(c.last_message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
            const active = Chat.threadUserId === u.id ? ' active' : '';
            const unreadCls = c.unread_count > 0 ? ' unread' : '';
            return `<button class="conv-item${unreadCls}${active}" onclick="ChatSystem.openThread(${u.id},'${esc(u.name)}','${esc(u.avatar || '')}')">
                <div class="conv-avatar">${avatarHtml(u.name, u.avatar, 46)}</div>
                <div class="conv-item-info">
                    <strong>${esc(u.name)}</strong>
                    <span>${last}</span>
                </div>
                <div class="conv-item-meta">
                    <span class="conv-time">${time}</span>
                    ${unread}
                </div>
            </button>`;
        }).join('');
    } catch (_) {
        list.innerHTML = '<div class="conv-empty"><i class="fas fa-exclamation-circle"></i><p>Could not load conversations.</p></div>';
    }
}

async function openThread(userId, name, avatar) {
    Chat.threadUserId = Number(userId);
    Chat.threadUserName = name || 'Conversation';
    Chat.threadUserAvatar = avatar || '';
    stopPolling();

    const nameEl   = document.getElementById('threadName');
    const avatarEl = document.getElementById('threadAvatar');
    const subEl    = document.getElementById('threadSub');
    if (nameEl)   nameEl.textContent = Chat.threadUserName;
    if (avatarEl) avatarEl.innerHTML = avatarHtml(Chat.threadUserName, Chat.threadUserAvatar, 40);
    if (subEl)    subEl.textContent  = 'Active now';

    document.getElementById('threadEmptyState').style.display = 'none';
    const tv = document.getElementById('threadView');
    if (tv) tv.style.display = 'flex';

    const msgNode = document.getElementById('threadMessages');
    if (msgNode) msgNode.innerHTML = '<div class="messages-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading…</p></div>';

    try {
        Chat.messages = await apiFetch('/api/messages/' + userId);
        renderMessages();
        refreshUnread();
        loadConversations();
    } catch (_) {
        if (msgNode) msgNode.innerHTML = '<div class="conv-empty"><p>Could not load messages.</p></div>';
    }

    startPolling();
}

function renderMessages() {
    const node = document.getElementById('threadMessages');
    if (!node) return;
    if (!Chat.messages.length) {
        node.innerHTML = '<div class="conv-empty" style="padding:48px 20px;"><i class="fas fa-comments" style="font-size:40px;opacity:.2;display:block;margin-bottom:12px;"></i><p>No messages yet. Say hello!</p></div>';
        return;
    }

    let html = '';
    let lastDate = '';

    Chat.messages.forEach(m => {
        const mine = m.sender_id === Chat.ME;
        const time = m.created_at ? new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
        const dateStr = m.created_at ? new Date(m.created_at).toLocaleDateString([], { weekday: 'long', month: 'short', day: 'numeric' }) : '';

        if (dateStr && dateStr !== lastDate) {
            html += `<div class="msg-date-divider">${dateStr}</div>`;
            lastDate = dateStr;
        }

        const senderName   = mine ? Chat.MY_NAME   : Chat.threadUserName;
        const senderAvatar = mine ? Chat.MY_AVATAR  : Chat.threadUserAvatar;

        html += `<div class="msg-row ${mine ? 'mine' : 'theirs'}">
            <div class="msg-row-avatar">${avatarHtml(senderName, senderAvatar, 28)}</div>
            <div class="msg-bubble ${mine ? 'mine' : 'theirs'}">
                ${esc(m.body)}
                ${time ? `<span class="msg-time">${time}</span>` : ''}
            </div>
        </div>`;
    });

    node.innerHTML = html;
    node.scrollTop = node.scrollHeight;
}

async function send() {
    const input    = document.getElementById('threadInput');
    const body     = input ? input.value.trim() : '';
    const receiver = Chat.threadUserId;
    if (!body || !receiver) return;
    if (input) { input.value = ''; input.style.height = 'auto'; }
    try {
        const sent = await apiFetch('/api/messages', {
            method: 'POST',
            body: JSON.stringify({ receiver_id: receiver, body }),
        });
        Chat.messages.push(sent);
        renderMessages();
        loadConversations();
    } catch (_) {
        if (input) input.value = body;
    }
}

function startPolling() {
    stopPolling();
    if (!Chat.threadUserId) return;
    Chat.pollTimer = setInterval(async () => {
        if (!Chat.threadUserId) return;
        try {
            const msgs = await apiFetch('/api/messages/' + Chat.threadUserId);
            if (msgs.length !== Chat.messages.length) {
                Chat.messages = msgs;
                renderMessages();
                loadConversations();
            }
        } catch (_) {}
    }, 2500);
}

function stopPolling() {
    if (Chat.pollTimer) { clearInterval(Chat.pollTimer); Chat.pollTimer = null; }
}

async function refreshUnread() {
    try {
        const data  = await apiFetch('/api/messages/unread-count');
        const badge = document.getElementById('notificationCount');
        if (badge) {
            const n = Number(data.count || 0);
            badge.textContent   = n;
            badge.style.display = n > 0 ? 'block' : 'none';
        }
    } catch (_) {}
}

let _searchTimer = null;
function onSearchInput(event) {
    clearTimeout(_searchTimer);
    const q   = event.target.value.trim();
    const box = document.getElementById('pageSearchResults');
    if (!box) return;
    if (q.length < 2) { box.style.display = 'none'; box.innerHTML = ''; return; }
    _searchTimer = setTimeout(async () => {
        try {
            const users = await apiFetch('/api/users/search?q=' + encodeURIComponent(q));
            if (!users.length) { box.style.display = 'none'; return; }
            box.innerHTML = users.map(u =>
                `<div class="user-result" onclick="ChatSystem.openThread(${u.id},'${esc(u.name)}','${esc(u.avatar || '')}')">
                    <div class="user-result-avatar">${avatarHtml(u.name, u.avatar, 38)}</div>
                    <div class="user-result-info">
                        <strong>${esc(u.name)}</strong>
                        <small>${esc(u.email || '')}</small>
                    </div>
                </div>`
            ).join('');
            box.style.display = 'block';
        } catch (_) { box.style.display = 'none'; }
    }, 250);
}

function subscribe() {
    const echo = ensureEcho();
    if (!echo || !Chat.ME) return;
    echo.private('chat.' + Chat.ME).listen('.new-message', msg => {
        if (Chat.threadUserId === msg.sender_id) {
            Chat.messages.push(msg);
            renderMessages();
        }
        loadConversations();
        refreshUnread();
    });
}

window.ChatSystem = {
    init() {
        Chat.cfg       = window.__dashboardConfig || {};
        Chat.ME        = Chat.cfg.userId || null;
        Chat.MY_NAME   = Chat.cfg.user?.name   || '';
        Chat.MY_AVATAR = Chat.cfg.user?.avatar  || '';

        subscribe();
        loadConversations();
        refreshUnread();

        const searchInput = document.getElementById('pageSearchInput');
        if (searchInput) searchInput.addEventListener('input', onSearchInput);

        document.addEventListener('click', e => {
            const box = document.getElementById('pageSearchResults');
            const inp = document.getElementById('pageSearchInput');
            if (box && inp && !inp.contains(e.target) && !box.contains(e.target)) {
                box.style.display = 'none';
            }
        });

        if (Chat.cfg.openUserId) {
            openThread(Chat.cfg.openUserId, Chat.cfg.openUserName || 'Conversation', Chat.cfg.openUserAvatar || '');
        }
    },
    openThread,
    send,
    handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
    },
    autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    },
    onSearchInput,
};

document.addEventListener('DOMContentLoaded', () => {
    const tv = document.getElementById('threadView');
    if (tv) tv.style.display = 'none';
    window.ChatSystem.init();
});
