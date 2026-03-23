import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const bodyData = document.body.dataset;
window.__dashboardConfig = {
    pusherKey: bodyData.pusherKey || "",
    pusherCluster: bodyData.pusherCluster || "mt1",
    userId: bodyData.chatUserId ? Number(bodyData.chatUserId) : null,
    user: {
        id: bodyData.chatUserId ? Number(bodyData.chatUserId) : null,
        name: bodyData.chatUserName || "",
        avatar: bodyData.chatUserAvatar || "",
    },
    openUserId: bodyData.chatOpenUserId ? Number(bodyData.chatOpenUserId) : null,
    openUserName: bodyData.chatOpenUserName || "",
    openUserAvatar: bodyData.chatOpenUserAvatar || "",
};

function ensureEcho() {
    if (window.Echo || !window.__dashboardConfig.userId || !window.__dashboardConfig.pusherKey) {
        return window.Echo || null;
    }

    window.Echo = new Echo({
        broadcaster: "pusher",
        key: window.__dashboardConfig.pusherKey,
        cluster: window.__dashboardConfig.pusherCluster,
        forceTLS: true,
        authEndpoint: "/broadcasting/auth",
        auth: {
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
            },
        },
    });

    return window.Echo;
}

const Chat = {
    currentThreadUserId: null,
    messages: [],
    unreadTotal: 0,
};

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
        },
        credentials: "same-origin",
        ...options,
    });

    if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
    }

    return res.json();
}

function esc(v) {
    if (!v) return "";
    return String(v)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

window.ChatSystem = {
    async init() {
        this.bindSidebarActions();
        this.subscribe();
        await this.loadConversations();
        await this.refreshUnread();

        if (window.__dashboardConfig.openUserId) {
            this.openThread(
                window.__dashboardConfig.openUserId,
                window.__dashboardConfig.openUserName || "Conversation",
            );
        }
    },

    bindSidebarActions() {
        window.toggleSidebar = function toggleSidebar() {
            document.getElementById("sidebar")?.classList.toggle("open");
        };
        window.viewProfile = function viewProfile() {
            window.location.href = "/profile";
        };
        window.openSettings = function openSettings() {
            window.location.href = "/settings";
        };
        window.logout = async function logout() {
            await fetch("/logout", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                },
            });
            window.location.href = "/login";
        };
    },

    subscribe() {
        const echo = ensureEcho();
        const userId = window.__dashboardConfig.userId;
        if (!echo || !userId) return;

        echo.private(`chat.${userId}`).listen(".new-message", (message) => {
            if (Chat.currentThreadUserId === message.sender_id) {
                Chat.messages.push(message);
                this.renderMessages();
            } else {
                this.refreshUnread();
            }
            this.loadConversations();
        });
    },

    async loadConversations() {
        const list = document.getElementById("pageConvList");
        try {
            const conversations = await apiFetch("/api/conversations");
            if (!conversations.length) {
                list.innerHTML = '<div class="conv-empty"><i class="fas fa-comment-slash"></i>No conversations yet</div>';
                return;
            }

            list.innerHTML = conversations
                .map((c) => {
                    const u = c.user;
                    const unread = c.unread_count > 0 ? `<span class="badge">${c.unread_count}</span>` : "";
                    return `
                        <button class="conv-item" onclick="ChatSystem.openThread(${u.id}, '${esc(u.name)}')">
                            <div class="conv-name">${esc(u.name)}</div>
                            <div class="conv-last">${esc(c.last_message?.body || "")}</div>
                            ${unread}
                        </button>
                    `;
                })
                .join("");
        } catch (_e) {
            list.innerHTML = '<div class="conv-empty"><i class="fas fa-exclamation-circle"></i>Could not load chats</div>';
        }
    },

    async openThread(userId, name = "Conversation") {
        Chat.currentThreadUserId = Number(userId);
        document.getElementById("threadName").textContent = name;
        document.getElementById("threadEmptyState").style.display = "none";
        document.getElementById("threadView").style.display = "block";

        try {
            Chat.messages = await apiFetch(`/api/messages/${userId}`);
            this.renderMessages();
            await this.refreshUnread();
        } catch (_e) {
            document.getElementById("threadMessages").innerHTML = '<div class="conv-empty">Could not load messages.</div>';
        }
    },

    renderMessages() {
        const me = window.__dashboardConfig.userId;
        const node = document.getElementById("threadMessages");
        node.innerHTML = Chat.messages
            .map((m) => {
                const mine = m.sender_id === me;
                return `<div class="msg ${mine ? "mine" : "theirs"}">${esc(m.body)}</div>`;
            })
            .join("");
        node.scrollTop = node.scrollHeight;
    },

    handleKey(e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            this.send();
        }
    },

    autoResize(el) {
        el.style.height = "auto";
        el.style.height = `${Math.min(el.scrollHeight, 120)}px`;
    },

    async send() {
        const input = document.getElementById("threadInput");
        const body = input.value.trim();
        const receiverId = Chat.currentThreadUserId;
        if (!body || !receiverId) return;

        input.value = "";
        try {
            const sent = await apiFetch("/api/messages", {
                method: "POST",
                body: JSON.stringify({ receiver_id: receiverId, body }),
            });
            Chat.messages.push(sent);
            this.renderMessages();
            this.loadConversations();
        } catch (_e) {
            input.value = body;
        }
    },

    _searchTimer: null,
    onSearchInput(event) {
        clearTimeout(this._searchTimer);
        const q = event.target.value.trim();
        const box = document.getElementById("pageSearchResults");
        if (q.length < 2) {
            box.style.display = "none";
            box.innerHTML = "";
            return;
        }
        this._searchTimer = setTimeout(async () => {
            try {
                const users = await apiFetch(`/api/users/search?q=${encodeURIComponent(q)}`);
                if (!users.length) {
                    box.style.display = "none";
                    return;
                }
                box.innerHTML = users
                    .map((u) => `<div class="search-item" onclick="ChatSystem.openThread(${u.id}, '${esc(u.name)}')">${esc(u.name)}</div>`)
                    .join("");
                box.style.display = "block";
            } catch (_e) {
                box.style.display = "none";
            }
        }, 250);
    },

    async refreshUnread() {
        try {
            const data = await apiFetch("/api/messages/unread-count");
            Chat.unreadTotal = Number(data.count || 0);
        } catch (_e) {
            Chat.unreadTotal = 0;
        }
    },
};

document.addEventListener("DOMContentLoaded", () => {
    const threadView = document.getElementById("threadView");
    if (threadView) {
        threadView.style.display = "none";
    }
    window.ChatSystem.init();
});
