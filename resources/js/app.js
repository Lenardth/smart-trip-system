import Alpine from 'alpinejs';
import './blade/base';
import './blade/global';
import './bootstrap';

window.Alpine = Alpine;
Alpine.start();

const rawPath = window.location.pathname.replace(/\/$/, '') || '/';

const loaders = {
    '/':               () => import('./blade/landing/index.js'),
    '/dashboard':      () => import('./blade/dashboard/index.js'),
    '/discover':       () => import('./blade/discover/index.js'),
    '/plan-trip':      () => import('./blade/plan-trip/index.js'),
    '/wishlist':       () => import('./blade/wishlist/index.js'),
    '/community':      () => import('./blade/community/index.js'),
    '/destinations':   () => import('./blade/destinations/index.js'),
    '/accommodations': () => import('./blade/accommodations/index.js'),
    '/bookings':       () => import('./blade/bookings/index.js'),
    '/flights':        () => import('./blade/flights/index.js'),
    '/chat':           () => import('./blade/chat/index.js'),
    '/notifications':  () => import('./blade/notifications/index.js'),
    '/profile':        () => import('./blade/profile/edit.js'),
    '/profile/edit':   () => import('./blade/profile/edit.js'),
    '/login':          () => import('./blade/login.js'),
    '/register':       () => import('./blade/auth/register.js'),
};

const loader = loaders[rawPath]
    ?? Object.entries(loaders).find(([key]) => key !== '/' && rawPath.startsWith(key + '/'))?.[1];

if (loader) {
    loader().catch(err => console.error('[app] Failed to load page script:', err));
}
