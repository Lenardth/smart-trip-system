import Alpine from 'alpinejs';
import './blade/global';
import './blade/shared/currency';
import './bootstrap';

window.Alpine = Alpine;
Alpine.start();

const path = window.location.pathname.replace(/\/$/, '') || '/';

const routes = {
    '/':              () => import('./blade/landing/index.js'),
    '/dashboard':     () => import('./blade/dashboard/index.js'),
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
    if (document.readyState !== 'loading') {
        const _orig = document.addEventListener.bind(document);
        document.addEventListener = function(type, fn, opts) {
            if (type === 'DOMContentLoaded') {
                try { fn(); } catch (e) { console.error(e); }
            } else {
                _orig(type, fn, opts);
            }
        };
        loader().then(() => {
            document.addEventListener = _orig;
        }).catch(err => console.error('[app] page module failed:', err));
    } else {
        loader().catch(err => console.error('[app] page module failed:', err));
    }
}
