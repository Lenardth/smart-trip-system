function ensureEcho() {
    if (window.Echo) return window.Echo;
    const cfg = window.__dashboardConfig;
    window.Pusher.logToConsole = false;
    window.Echo = new window.LaravelEcho({
        broadcaster:  'pusher',
        key:          cfg.pusherKey,
        cluster:      cfg.pusherCluster,
        forceTLS:     true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        },
    });
    return window.Echo;
}

const Chat = {
    currentThreadUserId: null,
    conversations: [],
    messages: [],
    echoChannel: null,
    unreadTotal: 0,
};

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        credentials: 'same-origin',
        ...options,
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

function injectChatUI() {
    if (document.getElementById('chatOverlay')) return;

    document.head.insertAdjacentHTML('beforeend', `
<style>
#chatOverlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9000;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(3px);
    align-items: flex-end;
    justify-content: flex-end;
    padding: 20px;
    gap: 12px;
}
#chatOverlay.open { display: flex; }
#chatPanel {
    width: 320px;
    max-height: 540px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(59,31,43,.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: chatSlideUp .25s ease;
}
@keyframes chatSlideUp {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0);   opacity: 1; }
}
.chat-panel-header {
    background: linear-gradient(135deg,#3b1f2b,#6b3050);
    color: #fff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.chat-panel-header h3 { flex: 1; margin: 0; font-size: 15px; font-weight: 700; }
.chat-panel-header button {
    background: rgba(255,255,255,.15);
    border: none;
    color: #fff;
    width: 30px; height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 13px;
    display: flex; align-items: center; justify-content: center;
}
.chat-panel-header button:hover { background: rgba(255,255,255,.28); }
.chat-search { padding: 10px 12px; border-bottom: 1px solid #f0e8e0; }
.chat-search input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2d5c7;
    border-radius: 20px;
    font-size: 13px;
    outline: none;
    background: #faf7f4;
    box-sizing: border-box;
}
.chat-search input:focus { border-color: #c9a96e; }
.conversations-list { flex: 1; overflow-y: auto; }
.conversation-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background .15s;
    border-bottom: 1px solid #faf5f0;
}
.conversation-item:hover { background: #fdf8f4; }
.conversation-item.unread { background: #fef9f4; }
.conv-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg,#c9a96e,#b8955a);
    color: #fff; font-size: 16px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
}
.conv-avatar img { width: 100%; height: 100%; object-fit: cover; }
.conv-info { flex: 1; min-width: 0; }
.conv-info strong {
    display: block; font-size: 13px; color: #3b1f2b; font-weight: 600;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.conv-info span {
    font-size: 11px; color: #8a7a6f;
    display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.conv-meta { text-align: right; flex-shrink: 0; }
.conv-time { font-size: 10px; color: #b0a09a; }
.conv-badge {
    background: #c9a96e; color: #fff;
    border-radius: 10px; font-size: 10px; font-weight: 700;
    padding: 2px 6px; display: inline-block; margin-top: 3px;
}
.chat-empty { text-align: center; padding: 40px 20px; color: #b0a09a; font-size: 13px; }
.chat-empty i { font-size: 32px; display: block; margin-bottom: 8px; }
.new-chat-btn {
    margin: 10px 14px;
    padding: 9px;
    width: calc(100% - 28px);
    background: linear-gradient(135deg,#c9a96e,#b8955a);
    color: #fff; border: none; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.new-chat-btn:hover { opacity: .9; }
#threadPanel {
    width: 320px; max-height: 540px;
    background: #fff; border-radius: 16px;
    box-shadow: 0 20px 60px rgba(59,31,43,.25);
    display: none; flex-direction: column;
    overflow: hidden; animation: chatSlideUp .2s ease;
}
#threadPanel.open { display: flex; }
.thread-header {
    background: linear-gradient(135deg,#3b1f2b,#6b3050);
    color: #fff; padding: 12px 14px;
    display: flex; align-items: center; gap: 10px;
}
.thread-header button {
    background: rgba(255,255,255,.15); border: none; color: #fff;
    width: 28px; height: 28px; border-radius: 50%; cursor: pointer;
    font-size: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.thread-header button:hover { background: rgba(255,255,255,.28); }
.thread-header-info { flex: 1; min-width: 0; }
.thread-header-info strong {
    display: block; font-size: 14px; font-weight: 700;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.thread-header-info small { font-size: 11px; opacity: .75; }
.thread-messages {
    flex: 1; overflow-y: auto; padding: 12px;
    display: flex; flex-direction: column; gap: 6px;
    background: #fdf9f5;
}
.msg-bubble {
    max-width: 78%; padding: 8px 12px; border-radius: 14px;
    font-size: 13px; line-height: 1.45; word-break: break-word;
}
.msg-bubble.mine {
    background: linear-gradient(135deg,#c9a96e,#b8955a);
    color: #fff; align-self: flex-end; border-bottom-right-radius: 4px;
}
.msg-bubble.theirs {
    background: #fff; color: #3b1f2b; align-self: flex-start;
    border-bottom-left-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,.08);
}
.msg-time { font-size: 10px; opacity: .65; display: block; margin-top: 2px; text-align: right; }
.msg-bubble.theirs .msg-time { text-align: left; }
.thread-typing { padding: 4px 12px 8px; font-size: 11px; color: #b0a09a; min-height: 20px; }
.thread-input-row {
    display: flex; align-items: flex-end; gap: 8px;
    padding: 10px 12px; border-top: 1px solid #f0e8e0; background: #fff;
}
.thread-input-row textarea {
    flex: 1; border: 1px solid #e2d5c7; border-radius: 10px;
    padding: 8px 10px; font-size: 13px; resize: none;
    max-height: 80px; outline: none; font-family: inherit;
    background: #faf7f4; line-height: 1.4;
}
.thread-input-row textarea:focus { border-color: #c9a96e; }
.send-btn {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg,#c9a96e,#b8955a);
    border: none; color: #fff; font-size: 14px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: opacity .15s;
}
.send-btn:hover { opacity: .85; }
.send-btn:disabled { opacity: .4; cursor: not-allowed; }
.user-search-results {
    position: absolute; left: 0; right: 0; top: 100%;
    background: #fff; border: 1px solid #e2d5c7;
    border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
    z-index: 100; max-height: 200px; overflow-y: auto;
}
.user-result {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; cursor: pointer; transition: background .12s;
}
.user-result:hover { background: #fdf8f4; }
.user-result strong { font-size: 13px; color: #3b1f2b; display: block; }
.user-result span   { font-size: 11px; color: #8a7a6f; }
.chat-search-wrap   { position: relative; }
#chatBubble {
    position: fixed; bottom: 24px; right: 24px;
    width: 54px; height: 54px; border-radius: 50%;
    background: linear-gradient(135deg,#c9a96e,#b8955a);
    color: #fff; border: none; font-size: 22px; cursor: pointer;
    box-shadow: 0 4px 16px rgba(59,31,43,.3);
    display: flex; align-items: center; justify-content: center;
    z-index: 8999; transition: transform .2s;
}
#chatBubble:hover { transform: scale(1.08); }
#chatBubbleBadge {
    position: absolute; top: -4px; right: -4px;
    background: #f44336; color: #fff;
    border-radius: 10px; font-size: 10px; font-weight: 700;
    padding: 2px 5px; min-width: 16px; text-align: center; display: none;
}
</style>`);

    document.body.insertAdjacentHTML('beforeend', `
<button id="chatBubble" onclick="ChatSystem.toggle()" title="Messages">
    <i class="fas fa-comment-dots"></i>
    <span id="chatBubbleBadge"></span>
</button>

<div id="chatOverlay" onclick="ChatSystem.handleOverlayClick(event)">
    <div id="threadPanel">
        <div class="thread-header">
            <button onclick="ChatSystem.closeThread()"><i class="fas fa-arrow-left"></i></button>
            <div class="thread-header-info">
                <strong id="threadName">—</strong>
                <small id="threadStatus"></small>
            </div>
            <button onclick="ChatSystem.close()"><i class="fas fa-times"></i></button>
        </div>
        <div class="thread-messages" id="threadMessages"></div>
        <div class="thread-typing" id="threadTyping"></div>
        <div class="thread-input-row">
            <textarea id="threadInput" rows="1" placeholder="Type a message…"
                onkeydown="ChatSystem.handleKey(event)"
                oninput="ChatSystem.autoResize(this)"></textarea>
            <button class="send-btn" id="sendBtn" onclick="ChatSystem.send()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <div id="chatPanel">
        <div class="chat-panel-header">
            <i class="fas fa-comment-dots"></i>
            <h3>Messages</h3>
            <button onclick="ChatSystem.close()"><i class="fas fa-times"></i></button>
        </div>
        <div class="chat-search">
            <div class="chat-search-wrap">
                <input type="text" id="chatSearchInput" placeholder="Search or start new chat…"
                    oninput="ChatSystem.onSearchInput(event)" autocomplete="off">
                <div id="userSearchResults" class="user-search-results" style="display:none;"></div>
            </div>
        </div>
        <div class="conversations-list" id="conversationsList"></div>
        <button class="new-chat-btn" onclick="document.getElementById('chatSearchInput').focus()">
            <i class="fas fa-pen"></i> New Conversation
        </button>
    </div>
</div>
`);
}

window.ChatSystem = {
    init() {
        injectChatUI();
        this._subscribeToChannel();
        this._refreshUnreadBadge();
        setInterval(() => {
            if (document.getElementById('chatOverlay').classList.contains('open')) {
                this._loadConversations();
            }
        }, 60000);
    },

    _subscribeToChannel() {
        const echo   = ensureEcho();
        const userId = window.__dashboardConfig.userId;
        if (!userId) return;
        Chat.echoChannel = echo.private(`chat.${userId}`)
            .listen('.new-message', (data) => {
                this._onIncomingMessage(data);
            });
    },

    _onIncomingMessage(data) {
        if (Chat.currentThreadUserId === data.sender_id) {
            Chat.messages.push(data);
            this._renderMessages();
            this._scrollMessages();
            apiFetch(`/api/messages/${data.sender_id}`).catch(() => {});
        } else {
            Chat.unreadTotal++;
            this._updateBubbleBadge();
            if (document.getElementById('chatOverlay').classList.contains('open')) {
                this._loadConversations();
            }
            this._toast(`${data.sender?.name || 'Someone'}: ${data.body.substring(0, 60)}`);
        }
    },

    _toast(text) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'info', title: text,
            showConfirmButton: false, timer: 4000, timerProgressBar: true,
        });
    },

    toggle() {
        const overlay = document.getElementById('chatOverlay');
        if (overlay.classList.contains('open')) {
            this.close();
        } else {
            overlay.classList.add('open');
            this._loadConversations();
        }
    },

    close() {
        document.getElementById('chatOverlay').classList.remove('open');
        document.getElementById('threadPanel').classList.remove('open');
        Chat.currentThreadUserId = null;
    },

    handleOverlayClick(e) {
        if (e.target === document.getElementById('chatOverlay')) {
            this.close();
        }
    },

    async _loadConversations() {
        try {
            Chat.conversations = await apiFetch('/api/conversations');
            this._renderConversations();
        } catch (e) {
            document.getElementById('conversationsList').innerHTML =
                '<div class="chat-empty"><i class="fas fa-exclamation-circle"></i>Could not load conversations.</div>';
        }
    },

    _renderConversations() {
        const list = document.getElementById('conversationsList');
        if (!Chat.conversations.length) {
            list.innerHTML = `<div class="chat-empty">
                <i class="fas fa-comment-slash"></i>
                No conversations yet.<br>Search above to start chatting!
            </div>`;
            return;
        }
        list.innerHTML = Chat.conversations.map(c => {
            const u    = c.user;
            const lm   = c.last_message;
            const me   = window.__dashboardConfig.userId;
            const mine = lm.sender_id === me;
            const badge = c.unread_count > 0 ? `<span class="conv-badge">${c.unread_count}</span>` : '';
            const time  = this._relativeTime(lm.created_at);
            const initials = u.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
            const avatar = u.avatar
                ? `<img src="${u.avatar}" alt="${u.name}">`
                : initials;
            return `<div class="conversation-item ${c.unread_count > 0 ? 'unread' : ''}"
                         onclick="ChatSystem.openThread(${u.id}, '${this._esc(u.name)}', '${this._esc(u.avatar || '')}')">
                <div class="conv-avatar">${avatar}</div>
                <div class="conv-info">
                    <strong>${this._esc(u.name)}</strong>
                    <span>${mine ? 'You: ' : ''}${this._esc(lm.body)}</span>
                </div>
                <div class="conv-meta">
                    <div class="conv-time">${time}</div>
                    ${badge}
                </div>
            </div>`;
        }).join('');
    },

    async openThread(userId, name, avatar) {
        Chat.currentThreadUserId = userId;
        document.getElementById('threadName').textContent = name;
        document.getElementById('threadStatus').textContent = '';
        document.getElementById('threadMessages').innerHTML =
            '<div style="text-align:center;padding:20px;color:#b0a09a;font-size:12px;">Loading…</div>';
        document.getElementById('threadPanel').classList.add('open');
        document.getElementById('threadInput').focus();

        try {
            Chat.messages = await apiFetch(`/api/messages/${userId}`);
            this._renderMessages();
            this._scrollMessages();
            this._loadConversations();
            this._refreshUnreadBadge();
        } catch (e) {
            document.getElementById('threadMessages').innerHTML =
                '<div style="text-align:center;padding:20px;color:#b0a09a;">Could not load messages.</div>';
        }
    },

    closeThread() {
        document.getElementById('threadPanel').classList.remove('open');
        Chat.currentThreadUserId = null;
        this._loadConversations();
    },

    _renderMessages() {
        const me  = window.__dashboardConfig.userId;
        const box = document.getElementById('threadMessages');
        if (!Chat.messages.length) {
            box.innerHTML = `<div style="text-align:center;padding:30px 20px;color:#b0a09a;font-size:13px;">
                <i class="fas fa-comment" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                No messages yet. Say hello!
            </div>`;
            return;
        }
        box.innerHTML = Chat.messages.map(m => {
            const mine = m.sender_id === me;
            return `<div class="msg-bubble ${mine ? 'mine' : 'theirs'}">
                ${this._esc(m.body)}
                <span class="msg-time">${this._formatTime(m.created_at)}</span>
            </div>`;
        }).join('');
    },

    _scrollMessages() {
        const box = document.getElementById('threadMessages');
        box.scrollTop = box.scrollHeight;
    },

    handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.send();
        }
    },

    autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 80) + 'px';
    },

    async send() {
        const input  = document.getElementById('threadInput');
        const body   = input.value.trim();
        const userId = Chat.currentThreadUserId;
        if (!body || !userId) return;

        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        input.value  = '';
        input.style.height = 'auto';

        const optimistic = {
            id:          'tmp_' + Date.now(),
            sender_id:   window.__dashboardConfig.userId,
            receiver_id: userId,
            body:        body,
            created_at:  new Date().toISOString(),
        };
        Chat.messages.push(optimistic);
        this._renderMessages();
        this._scrollMessages();

        try {
            const saved = await apiFetch('/api/messages', {
                method: 'POST',
                body:   JSON.stringify({ receiver_id: userId, body }),
            });
            const idx = Chat.messages.findIndex(m => m.id === optimistic.id);
            if (idx !== -1) Chat.messages[idx] = saved;
            this._renderMessages();
            this._scrollMessages();
        } catch (e) {
            Chat.messages = Chat.messages.filter(m => m.id !== optimistic.id);
            this._renderMessages();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ toast: true, icon: 'error', title: 'Failed to send message',
                    position: 'bottom-end', showConfirmButton: false, timer: 3000 });
            }
        } finally {
            btn.disabled = false;
            input.focus();
        }
    },

    _searchTimer: null,

    onSearchInput(e) {
        clearTimeout(this._searchTimer);
        const q = e.target.value.trim();
        if (q.length < 2) {
            document.getElementById('userSearchResults').style.display = 'none';
            return;
        }
        this._searchTimer = setTimeout(() => this._searchUsers(q), 300);
    },

    async _searchUsers(q) {
        try {
            const users = await apiFetch(`/api/users/search?q=${encodeURIComponent(q)}`);
            const box = document.getElementById('userSearchResults');
            if (!users.length) { box.style.display = 'none'; return; }
            box.innerHTML = users.map(u => {
                const initials = u.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
                const avatar = u.avatar
                    ? `<div class="conv-avatar" style="width:32px;height:32px;font-size:12px;"><img src="${u.avatar}"></div>`
                    : `<div class="conv-avatar" style="width:32px;height:32px;font-size:12px;">${initials}</div>`;
                return `<div class="user-result"
                              onclick="ChatSystem._startNewChat(${u.id}, '${this._esc(u.name)}', '${this._esc(u.avatar || '')}')">
                    ${avatar}
                    <div>
                        <strong>${this._esc(u.name)}</strong>
                        <span>${this._esc(u.email)}</span>
                    </div>
                </div>`;
            }).join('');
            box.style.display = 'block';
        } catch (e) {}
    },

    _startNewChat(userId, name, avatar) {
        document.getElementById('userSearchResults').style.display = 'none';
        document.getElementById('chatSearchInput').value = '';
        this.openThread(userId, name, avatar);
    },

    async _refreshUnreadBadge() {
        try {
            const data = await apiFetch('/api/messages/unread-count');
            Chat.unreadTotal = data.count;
            this._updateBubbleBadge();
        } catch (e) {}
        setTimeout(() => this._refreshUnreadBadge(), 30000);
    },

    _updateBubbleBadge() {
        const badge = document.getElementById('chatBubbleBadge');
        if (Chat.unreadTotal > 0) {
            badge.textContent = Chat.unreadTotal > 99 ? '99+' : Chat.unreadTotal;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    },

    _esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    },

    _formatTime(iso) {
        return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    _relativeTime(iso) {
        const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
        if (diff < 60)    return 'now';
        if (diff < 3600)  return `${Math.floor(diff / 60)}m`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
        return `${Math.floor(diff / 86400)}d`;
    },
};

window.openComposeMessage = function () {
    const nd = document.getElementById('notificationDropdown');
    if (nd) nd.style.display = 'none';
    ChatSystem.toggle();
    setTimeout(() => {
        const inp = document.getElementById('chatSearchInput');
        if (inp) inp.focus();
    }, 150);
};

document.addEventListener('DOMContentLoaded', () => {
    ChatSystem.init();
});
