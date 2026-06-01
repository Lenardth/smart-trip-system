// Apply background images declared via data-bg attributes
document.querySelectorAll('[data-bg]').forEach(el => {
    el.style.backgroundImage = `url('${el.dataset.bg}')`;
});

// Handle broken nav avatar images without inline onerror
// Handle card images with data-fallback without inline onerror
document.addEventListener('error', (e) => {
    if (e.target.matches('.nav-avatar-img')) {
        e.target.classList.add('hidden');
        e.target.nextElementSibling?.classList.remove('nav-avatar-init--hidden');
    } else if (e.target.matches('.card-img-fallback') && e.target.dataset.fallback) {
        e.target.src = e.target.dataset.fallback;
        delete e.target.dataset.fallback;
    }
}, true);

// Read dashboard config from layout data attributes.
(function () {
    const data = document.body?.dataset || {};
    if (!data.userId && !data.pusherKey) return;

    window.__dashboardConfig = {
        pusherKey: data.pusherKey || '',
        pusherCluster: data.pusherCluster || 'mt1',
        userId: data.userId ? Number(data.userId) : null,
        user: {
            id: data.userId ? Number(data.userId) : null,
            name: data.userName || '',
            firstName: data.userFirstName || '',
            avatar: data.userAvatar || '',
            type: data.userType || '',
            verified: data.userVerified === 'true',
        },
    };
})();

window.App = {
    init() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    },
    showToast(message, type = 'info') {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        document.querySelector('.app-toast')?.remove();
        const toast = document.createElement('div');
        toast.className = `app-toast app-toast--${type}`;
        const icon = document.createElement('i');
        icon.className = `fas ${icons[type] || icons.info}`;
        const text = document.createElement('span');
        text.textContent = String(message || '');
        toast.append(icon, text);
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
};

window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('active');
};

window.viewProfile = function() {
    window.location.href = '/dashboard';
};

window.openSettings = function() {
    window.location.href = '/dashboard';
};

window.togglePublicNav = function() {
    document.getElementById('publicNav')?.classList.toggle('open');
};

window.logout = function() {
    const existing = document.querySelector('.logout-form');
    if (existing) {
        existing.submit();
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">`;
    document.body.appendChild(form);
    form.submit();
};

window.TravelContext = window.TravelContext || (() => {
    const STORAGE_KEY = 'smartTrip.travelContext';
    const MAX_AGE_MS = 30 * 60 * 1000;
    const CONTEXT_KEYS = ['destination', 'country', 'mood', 'budget', 'duration', 'companion', 'month', 'region', 'accommodation', 'origin', 'experience', 'departure_date', 'return_date'];
    const TRAVEL_PATHS = ['/discover', '/plan-trip', '/flights', '/accommodations'];

    function getValue(id) {
        const el = document.getElementById(id);
        return el ? String(el.value || '').trim() : '';
    }

    function setIfFilled(params, key, value) {
        const clean = String(value || '').trim();
        if (clean && clean !== 'any') params.set(key, clean);
    }

    function monthFromDate(value) {
        if (!value) return '';
        const date = new Date(value + 'T00:00:00');
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleString('en-US', { month: 'long' });
    }

    function durationFromDates(startValue, endValue) {
        if (!startValue || !endValue) return '';
        const start = new Date(startValue + 'T00:00:00');
        const end = new Date(endValue + 'T00:00:00');
        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return '';
        const days = Math.round((end - start) / 86400000) + 1;
        return days > 0 ? String(days) : '';
    }

    function currentContext() {
        const params = new URLSearchParams(window.location.search);
        const context = new URLSearchParams();
        const path = window.location.pathname.replace(/\/$/, '') || '/';

        Object.entries(load()).forEach(([key, value]) => setIfFilled(context, key, value));

        for (const key of CONTEXT_KEYS) {
            setIfFilled(context, key, params.get(key) || '');
        }
        setIfFilled(context, 'destination', params.get('q') || '');

        if (path === '/') {
            setIfFilled(context, 'mood', getValue('moodSelect'));
            setIfFilled(context, 'budget', getValue('budgetSelect'));
            setIfFilled(context, 'duration', getValue('durationSelect'));
            setIfFilled(context, 'companion', getValue('companionSelect'));
            setIfFilled(context, 'month', getValue('monthSelect'));
            setIfFilled(context, 'region', getValue('regionSelect'));
            setIfFilled(context, 'accommodation', getValue('accommodationSelect'));
            setIfFilled(context, 'origin', getValue('originInput'));
            setIfFilled(context, 'experience', getValue('experienceSelect'));
        } else if (path === '/flights') {
            setIfFilled(context, 'origin', getValue('from'));
            setIfFilled(context, 'destination', getValue('to'));
            setIfFilled(context, 'departure_date', getValue('departure_date'));
            setIfFilled(context, 'return_date', getValue('return_date'));
            setIfFilled(context, 'month', monthFromDate(getValue('departure_date')));
            setIfFilled(context, 'duration', durationFromDates(getValue('departure_date'), getValue('return_date')));
        } else if (path === '/accommodations') {
            setIfFilled(context, 'destination', getValue('searchInput'));
            setIfFilled(context, 'accommodation', getValue('styleSelect'));
            setIfFilled(context, 'budget', getValue('budgetSelect'));
        } else if (path === '/discover') {
            setIfFilled(context, 'destination', getValue('discoverSearchInput'));
            setIfFilled(context, 'region', getValue('discoverRegionFilter'));
            setIfFilled(context, 'mood', getValue('discoverMoodFilter'));
        }

        context.set('prefill', '1');
        return context;
    }

    function save(params) {
        const data = params instanceof URLSearchParams
            ? Object.fromEntries(params.entries())
            : params;

        sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
            at: Date.now(),
            data,
        }));
    }

    function load() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            if (!raw) return {};
            const saved = JSON.parse(raw);
            if (!saved.at || Date.now() - saved.at > MAX_AGE_MS) {
                sessionStorage.removeItem(STORAGE_KEY);
                return {};
            }
            return saved.data || {};
        } catch (e) {
            return {};
        }
    }

    function clear() {
        sessionStorage.removeItem(STORAGE_KEY);
    }

    function clearOnReload() {
        const navigation = performance.getEntriesByType?.('navigation')?.[0];
        if (navigation?.type !== 'reload') return;

        clear();

        const url = new URL(window.location.href);
        if (url.searchParams.get('prefill') !== '1') return;

        [...CONTEXT_KEYS, 'q', 'from', 'to', 'prefill'].forEach(key => {
            url.searchParams.delete(key);
        });

        window.history.replaceState({}, document.title, url.pathname + (url.search ? url.search : '') + url.hash);
    }

    function update(values) {
        const context = new URLSearchParams();
        Object.entries(load()).forEach(([key, value]) => setIfFilled(context, key, value));
        Object.entries(values || {}).forEach(([key, value]) => {
            const clean = String(value || '').trim();
            if (clean && clean !== 'any') context.set(key, clean);
            else context.delete(key);
        });
        context.set('prefill', '1');
        save(context);

        return Object.fromEntries(context.entries());
    }

    function buildUrl(url) {
        const target = new URL(url || '/plan-trip', window.location.origin);
        const path = target.pathname.replace(/\/$/, '') || '/';
        if (target.origin !== window.location.origin || !TRAVEL_PATHS.includes(path)) return target.toString();

        const context = currentContext();
        target.searchParams.forEach((value, key) => setIfFilled(context, key, value));
        save(context);

        const output = new URLSearchParams(target.searchParams);
        const keysForPath = path === '/flights'
            ? ['origin', 'destination', 'departure_date', 'return_date']
            : path === '/accommodations'
                ? ['destination', 'budget', 'accommodation']
                : path === '/discover'
                    ? ['destination', 'region', 'mood', 'budget', 'companion']
                    : CONTEXT_KEYS;

        keysForPath.forEach(key => setIfFilled(output, key, context.get(key) || ''));
        output.set('prefill', '1');

        return target.pathname + (output.toString() ? '?' + output.toString() : '');
    }

    return { buildUrl, load, clear, clearOnReload, currentContext, save, update };
})();

window.PlanTripContext = window.PlanTripContext || window.TravelContext;
window.TravelContext.clearOnReload();

document.addEventListener('click', function(e) {
    const travelLink = e.target.closest('a[href], [data-action="navigate"][data-url]');
    if (!travelLink || travelLink.target === '_blank') return;

    const href = travelLink.getAttribute('href') || travelLink.dataset.url || '';
    if (!href || href.startsWith('#')) return;

    const target = new URL(href, window.location.origin);
    const path = target.pathname.replace(/\/$/, '') || '/';
    if (target.origin !== window.location.origin || !['/discover', '/plan-trip', '/flights', '/accommodations'].includes(path)) return;

    e.preventDefault();
    e.stopImmediatePropagation();
    window.location.href = window.TravelContext.buildUrl(href);
}, true);

document.addEventListener('submit', function(e) {
    const button = e.submitter;
    const message = button?.dataset?.confirmSubmit;
    if (message && !window.confirm(message)) {
        e.preventDefault();
    }
});

function initPublicNavigation() {
    const btn = document.getElementById('mobHamburger');
    const drawer = document.getElementById('mobDrawer');
    const overlay = document.getElementById('mobOverlay');
    const closeBtn = document.getElementById('mobDrawerClose');

    if (!btn || !drawer || !overlay || drawer.dataset.publicNavReady === '1') {
        return;
    }

    drawer.dataset.publicNavReady = '1';

    function openDrawer() {
        drawer.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        btn.classList.add('open');
    }

    function closeDrawer() {
        drawer.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
        btn.classList.remove('open');
    }

    btn.addEventListener('click', openDrawer);
    closeBtn?.addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);
    drawer.querySelectorAll('.mob-link').forEach(link => {
        link.addEventListener('click', closeDrawer);
    });
}

// ── Custom Select (replaces all native <select> with the Travel-Mood style) ──

(function () {
    const ICON_MAP = {
        // Travel companion
        solo: 'fas fa-user', couple: 'fas fa-heart',
        family_young: 'fas fa-baby', family_teens: 'fas fa-child',
        friends_small: 'fas fa-user-friends', friends_large: 'fas fa-users',
        // Budget tiers
        backpacker: 'fas fa-suitcase', budget: 'fas fa-wallet',
        mid: 'fas fa-coins', premium: 'fas fa-star', luxury: 'fas fa-gem',
        // Months
        january: 'fas fa-snowflake', february: 'fas fa-heart',
        march: 'fas fa-seedling', april: 'fas fa-cloud-rain',
        may: 'fas fa-sun', june: 'fas fa-sun',
        july: 'fas fa-umbrella-beach', august: 'fas fa-umbrella-beach',
        september: 'fas fa-leaf', october: 'fas fa-leaf',
        november: 'fas fa-cloud', december: 'fas fa-snowflake',
        // Region
        any: 'fas fa-globe', europe: 'fas fa-landmark',
        southeast_asia: 'fas fa-map-marker-alt', east_asia: 'fas fa-torii-gate',
        south_asia: 'fas fa-mountain', middle_east: 'fas fa-mosque',
        africa: 'fas fa-sun', north_america: 'fas fa-flag',
        latin_america: 'fas fa-water', oceania: 'fas fa-water',
        central_america: 'fas fa-water', south_america: 'fas fa-water',
        caribbean: 'fas fa-water',
        // Accommodation style
        hostel: 'fas fa-bed', budget_hotel: 'fas fa-hotel',
        boutique: 'fas fa-building', resort: 'fas fa-umbrella-beach',
        villa: 'fas fa-home', airbnb: 'fas fa-key',
        glamping: 'fas fa-tree', bnb: 'fas fa-hotel', hotel: 'fas fa-hotel',
        // Experience level
        first_time: 'fas fa-seedling', occasional: 'fas fa-smile',
        regular: 'fas fa-route', frequent: 'fas fa-award',
        experienced: 'fas fa-award',
        // Flight class
        economy: 'fas fa-chair', premium_economy: 'fas fa-star',
        business: 'fas fa-briefcase', first: 'fas fa-crown',
        // Sort options
        price: 'fas fa-tag', duration: 'fas fa-clock',
        departure: 'fas fa-plane-departure', arrival: 'fas fa-plane-arrival',
        newest: 'fas fa-sort-amount-down', oldest: 'fas fa-sort-amount-up',
        'price-high': 'fas fa-sort-numeric-down-alt',
        'price-low': 'fas fa-sort-numeric-down',
    };

    function iconFor(value) {
        return ICON_MAP[(value || '').toLowerCase()] || null;
    }

    function escapeValue(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return String(value).replace(/["\\]/g, '\\$&');
    }

    function buildTriggerContent(trigger, value, label) {
        trigger.innerHTML = '';
        const icon = iconFor(value);
        if (icon) {
            const span = document.createElement('span');
            span.className = 'custom-select-icon';
            span.innerHTML = `<i class="${icon}"></i>`;
            trigger.appendChild(span);
        }
        const text = document.createElement('span');
        text.className = 'custom-select-text';
        text.textContent = label;
        trigger.appendChild(text);
        const arrow = document.createElement('i');
        arrow.className = 'fas fa-chevron-down custom-select-arrow';
        trigger.appendChild(arrow);
    }

    function wrapSelect(sel) {
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';

        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger';
        trigger.tabIndex = sel.disabled ? -1 : 0;
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const dropdown = document.createElement('div');
        dropdown.className = 'custom-select-dropdown';
        dropdown.setAttribute('role', 'listbox');

        Array.from(sel.options).forEach(opt => {
            const item = document.createElement('div');
            item.className         = 'custom-select-option' + (opt.selected ? ' selected' : '');
            item.dataset.value     = opt.value;
            const baseLabel        = opt.textContent.trim();
            item.dataset.baseLabel = baseLabel;
            item.dataset.label     = baseLabel;
            // Carry USD range for budget selects
            if (opt.dataset.usdMin) item.dataset.usdMin = opt.dataset.usdMin;
            if (opt.dataset.usdMax) item.dataset.usdMax = opt.dataset.usdMax;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
            const icon = iconFor(opt.value);
            if (icon) {
                item.innerHTML = `<i class="${icon}"></i>`;
                item.appendChild(document.createTextNode(' ' + baseLabel));
            } else {
                item.textContent = baseLabel;
            }
            dropdown.appendChild(item);
        });

        const cur = sel.options[sel.selectedIndex];
        buildTriggerContent(trigger, cur ? cur.value : '', cur ? cur.textContent.trim() : '');

        sel.parentNode.insertBefore(wrapper, sel);
        wrapper.appendChild(trigger);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(sel);
        sel.style.display = 'none';
        sel.tabIndex = -1;
        sel.setAttribute('aria-hidden', 'true');
        sel.dataset.customInit = '1';

        if (sel.disabled) {
            wrapper.classList.add('disabled');
            trigger.setAttribute('aria-disabled', 'true');
        }

        function closeSelect() {
            wrapper.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        function openSelect() {
            if (sel.disabled) return;
            document.querySelectorAll('.custom-select-wrapper.open').forEach(w => {
                if (w !== wrapper) {
                    w.classList.remove('open');
                    w.querySelector('.custom-select-trigger')?.setAttribute('aria-expanded', 'false');
                }
            });
            wrapper.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            dropdown.querySelector('.selected')?.scrollIntoView({ block: 'nearest' });
        }

        function toggleSelect() {
            if (wrapper.classList.contains('open')) {
                closeSelect();
            } else {
                openSelect();
            }
        }

        function selectItem(item) {
            if (!item) return;
            dropdown.querySelectorAll('.custom-select-option').forEach(o => {
                o.classList.remove('selected');
                o.setAttribute('aria-selected', 'false');
            });
            item.classList.add('selected');
            item.setAttribute('aria-selected', 'true');
            buildTriggerContent(trigger, item.dataset.value, item.dataset.label);
            sel.value = item.dataset.value;
            sel.dispatchEvent(new Event('change', { bubbles: true }));
            closeSelect();
        }

        trigger.addEventListener('click', e => {
            e.stopPropagation();
            toggleSelect();
        });

        trigger.addEventListener('keydown', e => {
            if (['Enter', ' ', 'ArrowDown'].includes(e.key)) {
                e.preventDefault();
                openSelect();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeSelect();
            }
        });

        dropdown.addEventListener('click', e => {
            e.stopPropagation();
            const item = e.target.closest('.custom-select-option');
            selectItem(item);
        });

        dropdown.addEventListener('keydown', e => {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            selectItem(e.target.closest('.custom-select-option'));
        });

        sel.addEventListener('change', () => {
            const item = dropdown.querySelector(`.custom-select-option[data-value="${escapeValue(sel.value)}"]`);
            if (!item) return;
            dropdown.querySelectorAll('.custom-select-option').forEach(o => {
                o.classList.remove('selected');
                o.setAttribute('aria-selected', 'false');
            });
            item.classList.add('selected');
            item.setAttribute('aria-selected', 'true');
            buildTriggerContent(trigger, item.dataset.value, item.dataset.label);
        });
    }

    // ── Budget label formatting (converts USD tiers to user's currency) ───

    function fmtBudgetAmount(usd) {
        if (!window.Currency) return '$' + Math.round(usd).toLocaleString('en-US');
        const converted = window.Currency.convert(parseFloat(usd));
        return window.Currency.symbol() + Math.round(converted).toLocaleString('en-US');
    }

    function buildBudgetLabel(baseName, usdMin, usdMax) {
        if (usdMin && usdMax) return `${baseName} (${fmtBudgetAmount(usdMin)} to ${fmtBudgetAmount(usdMax)})`;
        if (usdMax)           return `${baseName} (under ${fmtBudgetAmount(usdMax)})`;
        if (usdMin)           return `${baseName} (${fmtBudgetAmount(usdMin)}+)`;
        return baseName;
    }

    window.refreshBudgetLabels = function () {
        document.querySelectorAll('.custom-select-option').forEach(item => {
            if (!item.dataset.usdMax && !item.dataset.usdMin) return;
            const label = buildBudgetLabel(item.dataset.baseLabel, item.dataset.usdMin, item.dataset.usdMax);
            item.dataset.label = label;
            const icon = item.querySelector('i');
            item.innerHTML = '';
            if (icon) {
                item.appendChild(icon);
                item.appendChild(document.createTextNode(' ' + label));
            } else {
                item.textContent = label;
            }
            if (item.classList.contains('selected')) {
                const trig = item.closest('.custom-select-wrapper')?.querySelector('.custom-select-trigger');
                if (trig) buildTriggerContent(trig, item.dataset.value, label);
            }
        });
    };

    document.addEventListener('currency:changed',      () => window.refreshBudgetLabels());
    document.addEventListener('currency:rates-loaded', () => window.refreshBudgetLabels());

    let closeListenerAdded = false;

    window.initCustomSelects = function () {
        document.querySelectorAll('select:not([data-custom-init])').forEach(sel => {
            if (sel.closest('.custom-select-wrapper')) return;
            wrapSelect(sel);
        });
        if (!closeListenerAdded) {
            document.addEventListener('click', () => {
                document.querySelectorAll('.custom-select-wrapper.open').forEach(w => {
                    w.classList.remove('open');
                    w.querySelector('.custom-select-trigger')?.setAttribute('aria-expanded', 'false');
                });
            });
            document.addEventListener('keydown', e => {
                if (e.key !== 'Escape') return;
                document.querySelectorAll('.custom-select-wrapper.open').forEach(w => {
                    w.classList.remove('open');
                    w.querySelector('.custom-select-trigger')?.setAttribute('aria-expanded', 'false');
                });
            });
            closeListenerAdded = true;
        }
        if (window.Currency) window.refreshBudgetLabels();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initCustomSelects);
    } else {
        window.initCustomSelects();
    }
})();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        App.init();
        initPublicNavigation();
    });
} else {
    App.init();
    initPublicNavigation();
}
