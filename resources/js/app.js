import './blade/global';
import './blade/shared/currency';
import './blade/shared/event-delegation';
import './bootstrap';

// Initialize event delegation system
import { init as initEventDelegation } from './blade/shared/event-delegation';
initEventDelegation();

const path = window.location.pathname.replace(/\/$/, '') || '/';

const routes = {
    '/':              () => import('./blade/landing/index.js'),
    '/dashboard':     () => import('./blade/dashboard/index.js'),
    '/discover':      () => import('./blade/discover/index.js'),
    '/plan-trip':     () => import('./blade/plan-trip/index.js'),
    '/accommodations':() => import('./blade/accommodations/index.js'),
    '/bookings':      () => import('./blade/bookings/index.js'),
    '/flights':       () => import('./blade/flights/index.js'),
    '/login':         () => import('./blade/auth/login.js'),
    '/register':      () => import('./blade/auth/register.js'),
    '/forgot-password': () => import('./blade/auth/login.js'),
};

const loader = routes[path] ??
    Object.entries(routes).find(([key]) =>
        key !== '/' && path.startsWith(key + '/')
    )?.[1];

if (loader) {
    loader().catch(err => console.error('[app] page module failed:', err));
}
