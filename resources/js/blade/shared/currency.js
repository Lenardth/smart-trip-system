/**
 * Currency module — loaded globally on every page.
 * Exposes window.Currency for use by other modules.
 */
(function () {
    const STORAGE_KEY = 'sb_currency';
    const API_RATES   = '/api/currency/rates';
    const API_SET     = '/api/currency/set';

    const SYMBOLS = {
        USD:'$', EUR:'€', GBP:'£', ZAR:'R', AED:'د.إ', JPY:'¥',
        AUD:'A$', CAD:'C$', CHF:'Fr', CNY:'¥', INR:'₹', BRL:'R$',
        MXN:'$', SGD:'S$', THB:'฿', KES:'KSh', NGN:'₦', EGP:'E£',
        IDR:'Rp', MYR:'RM', NZD:'NZ$', SEK:'kr', NOK:'kr', DKK:'kr',
        TRY:'₺', HUF:'Ft', PLN:'zł', CZK:'Kč',
    };

    const NO_DECIMALS = ['JPY','IDR','KES','NGN'];

    let _rates    = { USD: 1 };
    let _currency = localStorage.getItem(STORAGE_KEY) || 'USD';
    let _loaded   = false;

    async function loadRates() {
        try {
            const res  = await fetch(API_RATES + '?base=USD', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.rates) {
                _rates  = data.rates;
                _loaded = true;
                refreshPrices();
            }
        } catch (e) {
            console.warn('[Currency] Failed to load rates:', e.message);
        }
    }

    function convert(usd) {
        const rate = _rates[_currency] || 1;
        return usd * rate;
    }

    function format(usd) {
        const amount   = convert(usd);
        const symbol   = SYMBOLS[_currency] || _currency;
        const decimals = NO_DECIMALS.includes(_currency) ? 0 : 2;
        return symbol + amount.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }

    function symbol() {
        return SYMBOLS[_currency] || _currency;
    }

    function current() {
        return _currency;
    }

    function refreshPrices() {
        document.querySelectorAll('[data-price-usd]').forEach(el => {
            const usd = parseFloat(el.dataset.priceUsd);
            if (!isNaN(usd)) el.textContent = format(usd);
        });
    }

    async function setCurrency(code) {
        _currency = code;
        localStorage.setItem(STORAGE_KEY, code);

        document.querySelectorAll('.currency-option').forEach(el => {
            const active = el.dataset.code === code;
            el.classList.toggle('active', active);
            el.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        document.querySelectorAll('[data-currency-label]').forEach(el => {
            el.textContent = code;
        });

        try {
            await fetch(API_SET, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body:    JSON.stringify({ currency: code }),
            });
        } catch (e) { /* non-critical */ }

        refreshPrices();
        document.dispatchEvent(new CustomEvent('currency:changed', { detail: { currency: code } }));
    }

    function buildPicker(currencies, wrapper) {
        if (!wrapper || wrapper.dataset.currencyBuilt === '1') return;
        wrapper.dataset.currencyBuilt = '1';

        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'currency-btn';
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<i class="fas fa-coins"></i> <span data-currency-label>' + _currency + '</span> <i class="fas fa-chevron-down currency-chevron"></i>';

        const dropdown = document.createElement('div');
        dropdown.className = 'currency-dropdown';
        dropdown.setAttribute('role', 'listbox');

        Object.entries(currencies).forEach(([code, info]) => {
            const opt = document.createElement('button');
            opt.type             = 'button';
            opt.className        = 'currency-option' + (code === _currency ? ' active' : '');
            opt.dataset.code     = code;
            opt.setAttribute('role', 'option');
            opt.setAttribute('aria-selected', code === _currency ? 'true' : 'false');
            opt.innerHTML        = '<span class="currency-symbol">' + (info.symbol || code) + '</span><span class="currency-name">' + info.name + '</span><span class="currency-code">' + code + '</span>';
            opt.addEventListener('click', () => {
                setCurrency(code);
                dropdown.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            });
            dropdown.appendChild(opt);
        });

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.currency-dropdown.open').forEach(openDropdown => {
                if (openDropdown !== dropdown) {
                    openDropdown.classList.remove('open');
                    openDropdown.previousElementSibling?.setAttribute('aria-expanded', 'false');
                }
            });
            const isOpen = dropdown.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        dropdown.addEventListener('click', e => e.stopPropagation());

        document.addEventListener('click', () => {
            dropdown.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        });

        document.addEventListener('keydown', e => {
            if (e.key !== 'Escape') return;
            dropdown.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        });

        wrapper.appendChild(btn);
        wrapper.appendChild(dropdown);
    }

    function buildAllPickers(currencies) {
        document.querySelectorAll('.currency-picker-wrapper').forEach(wrapper => {
            buildPicker(currencies, wrapper);
        });
    }

    async function init() {
        try {
            const res  = await fetch(API_RATES + '?base=USD', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.rates) {
                _rates  = data.rates;
                _loaded = true;
            }
            if (data.currencies) buildAllPickers(data.currencies);
        } catch (e) {
            console.warn('[Currency] Init failed:', e.message);
        }

        refreshPrices();
        document.dispatchEvent(new CustomEvent('currency:rates-loaded', { detail: { currency: _currency } }));
    }

    window.Currency = { convert, format, symbol, current, setCurrency, refresh: refreshPrices };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
