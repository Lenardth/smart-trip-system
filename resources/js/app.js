import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const path = window.location.pathname;

const pageScripts = {
    '/':            () => import('./pages/landing.js'),
    '/dashboard':   () => import('./pages/dashboard.js'),
    '/discover':    () => import('./pages/discover.js'),
    '/plan-trip':   () => import('./pages/plan-trip.js'),
    '/wishlist':    () => import('./pages/wishlist.js'),
    '/community':   () => import('./pages/community.js'),
    '/destinations':() => import('./pages/destinations.js'),
};

const load = pageScripts[path];

if (load) {
    load().catch(err => console.error('Failed to load page script:', err));
}
