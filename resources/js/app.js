import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const path = window.location.pathname;

const pageScripts = {
    '/':            () => import('./blade/landing/index.js'),
    '/dashboard':   () => import('./blade/dashboard/index.js'),
    '/discover':    () => import('./blade/discover/index.js'),
    '/plan-trip':   () => import('./blade/plan-trip/index.js'),
    '/wishlist':    () => import('./blade/wishlist/index.js'),
    '/community':   () => import('./blade/community/index.js'),
    '/destinations':() => import('./blade/destinations/index.js'),
};

const load = pageScripts[path];

if (load) {
    load().catch(err => console.error('Failed to load page script:', err));
}
