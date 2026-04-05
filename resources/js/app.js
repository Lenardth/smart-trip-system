import Alpine from 'alpinejs';
import './blade/base';
import './blade/global';
import './bootstrap';

window.Alpine = Alpine;
Alpine.start();

// ── Per-page dynamic imports ────────────────────────────────────────────────
// Only the module matching the current URL is loaded — keeps initial bundle tiny.

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
    '/settings':       () => import('./blade/profile/edit.js'),  // settings reuses profile JS
    '/login':          () => import('./blade/login.js'),
    '/register':       () => import('./blade/auth/register.js'),
    '/forgot-password':() => import('./blade/login.js'),
    '/reset-password': () => import('./blade/login.js'),
};

// Exact match first, then prefix match (e.g. /destinations/42, /chat/5)
const loader = routes[path]
    ?? Object.entries(routes).find(([key]) =>
        key !== '/' && path.startsWith(key + '/')
    )?.[1];

if (loader) {
    loader().catch(err => console.error('[app] page module failed:', err));
}
