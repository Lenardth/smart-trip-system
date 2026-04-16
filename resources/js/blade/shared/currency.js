/**
 * Global Currency Module
 * Handles real-time currency conversion across all pages.
 * Usage: window.Currency.format(amount, fromCurrency)
 */

const SUPPORTED = {
    USD: { name: 'US Dollar',         symbol: '$',   decimals: 2 },
    EUR: { name: 'Euro',              symbol: '€',   decimals: 2 },
    GBP: { name: 'British Pound',     symbol: '£',   decimals: 2 },
    ZAR: { name: 'South African Rand',symbol: 'R',   decimals: 2 },
    AED: { name: 'UAE Dirham',        symbol: 'د.إ', decimals: 2 },
    JPY: { name: 'Japanese Yen',      symbol: '¥',   decimals: 0 },
    AUD: { name: 'Australian Dollar', symbol: 'A$',  decimals: 2 },
    CAD: { name: 'Canadian Dollar',   symbol: 'C$',  decimals: 2 },
    CHF: { name: 'Swiss Franc',       symbol: 'Fr',  decimals: 2 },
    CNY: { name: 'Chinese Yuan',      symbol: '¥',   decimals: 2 },
    INR: { name: 'Indian Rupee',      symbol: '₹',   decimals: 2 },
    BRL: { name: 'Brazilian Real',    symbol: 'R$',  decimals: 2 },
    MXN: { name: 'Mexican Peso',      symbol: '$',   decimals: 2 },
    SGD: { name: 'Singapore Dollar',  symbol: 'S$',  decimals: 2 },
    THB: { name: 'Thai Baht',         symbol: '฿',   decimals: 2 },
    KES: { name: 'Kenyan Shilling',   symbol: 'KSh', decimals: 0 },
    NGN: { name: 'Nigerian Naira',    symbol: '₦',   decimals: 0 },
    EGP: { name: 'Egyptian Pound',    symbol: 'E£',  decimals: 2 },
    IDR: { name: 'Indonesian Rupiah', symbol: 'Rp',  decimals: 0 },
    MYR: { name: 'Malaysian Ringgit', symbol: 'RM',  decimals: 2 },
    NZD: { name: 'New Zealand Dollar',symbol: 'NZ$', decimals: 2 },
    SEK: { name: 'Swedish Krona',     symbol: 'kr',  decimals: 2 },
    NOK: { name: 'Norwegian Krone',   symbol: 'kr',  decimals: 2 },
    DKK: { name: 'Danish Krone',      symbol: 'kr',  decimals: 2 },
    TRY: { name: 'Turkish Lira',      symbol: '₺',   decimals: 2 },
    HUF: { name: 'Hungarian Forint',  symbol: 'Ft',  decimals: 0 },
    PLN: { name: 'Polish Zloty',      symbol: 'zł',  decimals: 2 },
    CZK: { name: 'Czech Koruna',      symbol: 'Kč',  decimals: 2 },
};

// Fallback rates (USD base) — used until API responds
const FALLBACK_RATES = {
    USD: 1.0,    EUR: 0.92,  GBP: 0.79,  ZAR: 18.5,
    AED: 3.67,   JPY: 149.5, AUD: 1.53,  CAD: 1.36,
    CHF: 0.89,   CNY: 7.24,  INR: 83.1,  BRL: 4.97,
    MXN: 17.2,   SGD: 1.34,  THB: 35.1,  KES: 129.0,
    NGN: 1550.0, EGP: 30.9,  IDR: 15600, MYR: 4.72,
    NZD: 1.63,   SEK: 10.4,  NOK: 10.6,  DKK: 6.89,
    TRY: 32.1,   HUF: 356.0, PLN: 4.02,  CZK: 23.1,
};

let rates      = { ...FALLBACK_RATES };
let activeCurrency = localStorage.getItem('preferred_currency') || 'USD';
let ratesLoaded    = false;
let ratesFetching  = false;
let onChangeCallbacks = [];

async function fetchRates() {
    if (ratesFetching) return;
    ratesFetching = true;
    try {
        const res  = await fetch('/api/currency/rates?base=USD', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success && data.rates) {
            rates = data.rates;
            ratesLoaded = true;
            // Re-render all price elements on the page
            refreshAllPrices();
        }
    } catch (_) {
        // Keep fallback rates
    } finally {
        ratesFetching = false;
    }
}

function convert(amountUsd, toCurrency) {
    const to = toCurrency || activeCurrency;
    if (to === 'USD') return amountUsd;
    return amountUsd * (rates[to] || 1);
}

function format(amountUsd, toCurrency) {
    const to  = toCurrency || activeCurrency;
    const amt = convert(amountUsd, to);
    const cfg = SUPPORTED[to] || { symbol: to, decimals: 2 };
    return cfg.symbol + amt.toLocaleString('en-US', {
        minimumFractionDigits: cfg.decimals,
        maximumFractionDigits: cfg.decimals,
    });
}

function symbol(currency) {
    return (SUPPORTED[currency || activeCurrency] || {}).symbol || (currency || activeCurrency);
}

async function setCurrency(currency) {
    activeCurrency = currency;
    localStorage.setItem('preferred_currency', currency);

    // Persist to server session
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        await fetch('/api/currency/set', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.content : '',
            },
            body: JSON.stringify({ currency }),
        });
    } catch (_) {}

    refreshAllPrices();
    onChangeCallbacks.forEach(function(cb) { cb(currency); });
    updateSwitcherUI();
}

function onCurrencyChange(cb) {
    onChangeCallbacks.push(cb);
}

// Re-render all [data-price-usd] elements on the page
function refreshAllPrices() {
    document.querySelectorAll('[data-price-usd]').forEach(function(el) {
        const usd = parseFloat(el.dataset.priceUsd);
        if (!isNaN(usd) && usd > 0) {
            // Preserve child elements (like /night span) — only update text node
            const firstChild = el.firstChild;
            if (firstChild && firstChild.nodeType === Node.TEXT_NODE) {
                firstChild.textContent = format(usd);
            } else {
                // Replace full content but keep any child spans after the price
                const children = Array.from(el.childNodes).filter(n => n.nodeType === Node.ELEMENT_NODE);
                el.textContent = format(usd);
                children.forEach(function(child) { el.appendChild(child); });
            }
        }
    });

    // Trigger page-specific refresh if available
    if (typeof window.__refreshFlightPrices === 'function') window.__refreshFlightPrices();
    if (typeof window.__refreshAccomPrices  === 'function') window.__refreshAccomPrices();
}

function updateSwitcherUI() {
    document.querySelectorAll('.currency-switcher-btn').forEach(function(btn) {
        const cfg = SUPPORTED[activeCurrency] || {};
        btn.innerHTML = '<i class="fas fa-coins"></i> ' + activeCurrency + ' ' + (cfg.symbol || '');
    });
    document.querySelectorAll('.currency-option').forEach(function(opt) {
        opt.classList.toggle('active', opt.dataset.currency === activeCurrency);
    });
}

function buildSwitcher(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const btn = document.createElement('div');
    btn.className = 'currency-switcher';
    btn.innerHTML =
        '<button class="currency-switcher-btn" id="currencyBtn" aria-label="Change currency">' +
            '<i class="fas fa-coins"></i> ' + activeCurrency + ' ' + symbol() +
        '</button>' +
        '<div class="currency-dropdown" id="currencyDropdown">' +
            '<div class="currency-search-wrap"><input type="text" class="currency-search" id="currencySearch" placeholder="Search currency..."></div>' +
            '<div class="currency-list" id="currencyList">' +
                Object.entries(SUPPORTED).map(function([code, cfg]) {
                    return '<button class="currency-option' + (code === activeCurrency ? ' active' : '') + '" data-currency="' + code + '">' +
                        '<span class="currency-code">' + code + '</span>' +
                        '<span class="currency-symbol">' + cfg.symbol + '</span>' +
                        '<span class="currency-name">' + cfg.name + '</span>' +
                    '</button>';
                }).join('') +
            '</div>' +
        '</div>';

    container.appendChild(btn);

    const dropdownBtn = btn.querySelector('#currencyBtn');
    const dropdown    = btn.querySelector('#currencyDropdown');
    const search      = btn.querySelector('#currencySearch');
    const list        = btn.querySelector('#currencyList');

    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('open');
        if (dropdown.classList.contains('open')) search.focus();
    });

    document.addEventListener('click', function() {
        dropdown.classList.remove('open');
    });

    dropdown.addEventListener('click', function(e) { e.stopPropagation(); });

    search.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        list.querySelectorAll('.currency-option').forEach(function(opt) {
            const text = opt.textContent.toLowerCase();
            opt.style.display = text.includes(q) ? '' : 'none';
        });
    });

    list.addEventListener('click', function(e) {
        const opt = e.target.closest('.currency-option');
        if (!opt) return;
        setCurrency(opt.dataset.currency);
        dropdown.classList.remove('open');
    });
}

// Init on load
(function init() {
    if (document.readyState !== 'loading') {
        fetchRates();
    } else {
        document.addEventListener('DOMContentLoaded', fetchRates);
    }
})();

window.Currency = { convert, format, symbol, setCurrency, onCurrencyChange, buildSwitcher, refreshAllPrices, get active() { return activeCurrency; } };
