import Alpine from 'alpinejs';
import './blade/base';
import './blade/global';
import './bootstrap';

window.Alpine = Alpine;
Alpine.start();

// ── Per-page dynamic imports ────────────────────────────────────────────────
const path = window.location.pathname.replace(/\/$/, '') || '/';

const routes = {
    '/':               () => import('./blade/landing/index.js'),
    '/dashboard':      () => import('./blade/dashboard/index.js'),
    '/discover':       () => import('./blade/discover/index.js'),
    '/destinations':   () => import('./blade/destinations/index.js'),
    '/plan-trip':      () => import('./blade/plan-trip/index.js'),
    '/wishlist':       () => import('./blade/wishlist/index.js'),
    '/community':      () => import('./blade/community/index.js'),
    '/accommodations': () => import('./blade/accommodations/index.js'),
    '/bookings':       () => import('./blade/bookings/index.js'),
    '/flights':        () => import('./blade/flights/index.js'),
    '/chat':           () => import('./blade/chat/index.js'),
    '/notifications':  () => import('./blade/notifications/index.js'),
    '/profile':        () => import('./blade/profile/edit.js'),
    '/settings':       () => import('./blade/profile/edit.js'),
    '/login':          () => import('./blade/login.js'),
    '/register':       () => import('./blade/auth/register.js'),
    '/forgot-password':() => import('./blade/login.js'),
    '/reset-password': () => import('./blade/login.js'),
};

const loader = routes[path]
    ?? Object.entries(routes).find(([key]) =>
        key !== '/' && path.startsWith(key + '/')
    )?.[1];

if (loader) {
    // Fire the module. If DOM is already ready, modules that use
    // document.addEventListener('DOMContentLoaded') will miss it —
    // so we patch DOMContentLoaded to fire immediately when already loaded.
    if (document.readyState !== 'loading') {
        // Monkey-patch so any module that calls addEventListener('DOMContentLoaded', fn)
        // gets fn() called synchronously right away.
        const _orig = document.addEventListener.bind(document);
        document.addEventListener = function (type, fn, opts) {
            if (type === 'DOMContentLoaded') {
                try { fn(); } catch(e) { console.error(e); }
            } else {
                _orig(type, fn, opts);
            }
        };
        loader().then(() => {
            // Restore original after module loaded
            document.addEventListener = _orig;
        }).catch(err => console.error('[app] page module failed:', err));
    } else {
        loader().catch(err => console.error('[app] page module failed:', err));
    }
}
