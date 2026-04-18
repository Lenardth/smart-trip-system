// Destination Show Page - Cost Calculator & Wishlist

(function () {
    // Get destination data from page
    const destData = window.__destinationData || {};
    const DEST = destData.name || '';
    const COUNTRY = destData.country || '';
    const DEST_ID = destData.id || 0;
    
    let costData = null;
    let selectedActivities = new Set();
    let currentTier = 'mid';

    // ── Currency helper ───────────────────────────────────────────────────
    function fmt(usd) {
        if (typeof window.Currency !== 'undefined') return window.Currency.format(Number(usd));
        return '$' + Number(usd).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // ── Load cost breakdown from Groq API ─────────────────────────────────
    window.loadCostBreakdown = function () {
        const duration = document.getElementById('costDuration');
        const days = duration ? parseInt(duration.value) : 7;
        const content = document.getElementById('costBreakdownContent');
        if (!content) return;

        content.innerHTML = '<div class="cost-loading"><i class="fas fa-spinner fa-spin"></i> Fetching real cost data for ' + DEST + '…</div>';

        fetch('/api/destination-cost?destination=' + encodeURIComponent(DEST) + '&country=' + encodeURIComponent(COUNTRY) + '&duration=' + days, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success) throw new Error(res.message || 'Failed');
            costData = res.data;
            // Default all activities as selected
            selectedActivities = new Set();
            (costData.activities || []).forEach(function (a, i) {
                if (a.included !== false) selectedActivities.add(i);
            });
            renderCostBreakdown();
            renderActivities();
        })
        .catch(function (err) {
            content.innerHTML = '<div class="cost-error"><i class="fas fa-exclamation-circle"></i> Could not load cost data. Please try again.</div>';
        });
    };

    // ── Render the cost breakdown ─────────────────────────────────────────
    function renderCostBreakdown() {
        const content = document.getElementById('costBreakdownContent');
        if (!content || !costData) return;

        const dc = costData.daily_costs || {};
        const days = costData.duration_days || 7;

        // Calculate dynamic total based on selected tier + selected activities
        const activitiesCost = calcActivitiesCost();
        const tierAccom = { budget: dc.budget_hotel || 40, mid: dc.mid_hotel || 100, luxury: dc.luxury_hotel || 250 };
        const tierFood  = { budget: dc.street_food || 8, mid: dc.mid_restaurant || 25, luxury: dc.fine_dining || 70 };
        const tierTrans = { budget: dc.local_transport || 5, mid: dc.taxi_rideshare || 15, luxury: (dc.car_rental || 50) };

        const accomTotal = tierAccom[currentTier] * days;
        const foodTotal  = tierFood[currentTier] * days;
        const transTotal = tierTrans[currentTier] * days;
        const miscTotal  = (costData.misc_daily_usd || 15) * days;
        const visaCost   = costData.visa_cost_usd || 0;
        const insurance  = costData.travel_insurance_usd || 35;
        const grandTotal = accomTotal + foodTotal + transTotal + miscTotal + activitiesCost + visaCost + insurance;

        // Update sidebar CTA price
        const ctaAmount = document.querySelector('.dest-cta-amount');
        if (ctaAmount) {
            ctaAmount.textContent = fmt(grandTotal);
            ctaAmount.removeAttribute('data-price-usd');
        }

        content.innerHTML =
            // Tier selector
            '<div class="cost-tier-selector">' +
                '<button class="cost-tier-btn' + (currentTier === 'budget' ? ' active' : '') + '" onclick="window.setCostTier(\'budget\')">' +
                    '<i class="fas fa-backpack"></i> Budget' +
                '</button>' +
                '<button class="cost-tier-btn' + (currentTier === 'mid' ? ' active' : '') + '" onclick="window.setCostTier(\'mid\')">' +
                    '<i class="fas fa-star-half-alt"></i> Mid-Range' +
                '</button>' +
                '<button class="cost-tier-btn' + (currentTier === 'luxury' ? ' active' : '') + '" onclick="window.setCostTier(\'luxury\')">' +
                    '<i class="fas fa-gem"></i> Luxury' +
                '</button>' +
            '</div>' +

            // Grand total banner
            '<div class="cost-grand-total">' +
                '<div class="cost-grand-label">Estimated Total (' + days + ' days, 1 person)</div>' +
                '<div class="cost-grand-amount" id="grandTotalDisplay">' + fmt(grandTotal) + '</div>' +
                '<div class="cost-grand-note">Based on your selected activities &amp; travel style</div>' +
            '</div>' +

            // Breakdown rows
            '<div class="cost-rows">' +
                costRow('fa-bed',        'Accommodation',  accomTotal,    fmt(tierAccom[currentTier]) + '/night × ' + days + ' nights') +
                costRow('fa-utensils',   'Food & Dining',  foodTotal,     fmt(tierFood[currentTier]) + '/day × ' + days + ' days') +
                costRow('fa-bus',        'Transport',      transTotal,    fmt(tierTrans[currentTier]) + '/day × ' + days + ' days') +
                costRow('fa-map-marked-alt', 'Activities', activitiesCost, selectedActivities.size + ' activities selected') +
                costRow('fa-shopping-bag', 'Misc & Shopping', miscTotal,  fmt(costData.misc_daily_usd || 15) + '/day') +
                (visaCost > 0 ? costRow('fa-passport', 'Visa', visaCost, 'One-time fee') : '') +
                costRow('fa-shield-alt', 'Travel Insurance', insurance,  'Recommended') +
            '</div>' +

            // Daily cost reference
            '<div class="cost-daily-ref">' +
                '<div class="cost-daily-title"><i class="fas fa-info-circle"></i> Daily Cost Reference</div>' +
                '<div class="cost-daily-grid">' +
                    dailyRef('fa-bed',      'Budget Hotel',   dc.budget_hotel) +
                    dailyRef('fa-hotel',    'Mid Hotel',      dc.mid_hotel) +
                    dailyRef('fa-concierge-bell', 'Luxury Hotel', dc.luxury_hotel) +
                    dailyRef('fa-utensils', 'Street Food',    dc.street_food) +
                    dailyRef('fa-coffee',   'Restaurant',     dc.mid_restaurant) +
                    dailyRef('fa-bus',      'Local Transport',dc.local_transport) +
                    dailyRef('fa-car',      'Taxi/Rideshare', dc.taxi_rideshare) +
                '</div>' +
            '</div>' +

            // Tips
            (costData.tips && costData.tips.length ?
                '<div class="cost-tips">' +
                    '<div class="cost-tips-title"><i class="fas fa-lightbulb"></i> Money-Saving Tips</div>' +
                    costData.tips.map(function (t) {
                        return '<div class="cost-tip"><i class="fas fa-check-circle"></i> ' + esc(t) + '</div>';
                    }).join('') +
                '</div>'
            : '');
    }

    function costRow(icon, label, amount, note) {
        return '<div class="cost-row">' +
            '<div class="cost-row-left">' +
                '<i class="fas ' + icon + '"></i>' +
                '<div>' +
                    '<div class="cost-row-label">' + label + '</div>' +
                    '<div class="cost-row-note">' + note + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="cost-row-amount">' + fmt(amount) + '</div>' +
        '</div>';
    }

    function dailyRef(icon, label, amount) {
        if (!amount) return '';
        return '<div class="cost-daily-item">' +
            '<i class="fas ' + icon + '"></i>' +
            '<span>' + label + '</span>' +
            '<strong>' + fmt(amount) + '</strong>' +
        '</div>';
    }

    // ── Render activities ─────────────────────────────────────────────────
    function renderActivities() {
        const card = document.getElementById('activitiesCard');
        const content = document.getElementById('activitiesContent');
        if (!card || !content || !costData) return;

        const activities = costData.activities || [];
        if (!activities.length) return;

        card.style.display = 'block';

        const CATEGORY_ICONS = {
            culture: 'fa-landmark', nature: 'fa-tree', adventure: 'fa-hiking',
            food: 'fa-utensils', relaxation: 'fa-spa', shopping: 'fa-shopping-bag',
            nightlife: 'fa-music', sport: 'fa-running'
        };

        content.innerHTML = '<div class="activities-grid">' +
            activities.map(function (a, i) {
                const isSelected = selectedActivities.has(i);
                const icon = CATEGORY_ICONS[a.category] || 'fa-map-marker-alt';
                return '<div class="activity-item' + (isSelected ? ' selected' : '') + '" id="activity-' + i + '" onclick="window.toggleActivity(' + i + ')">' +
                    '<div class="activity-toggle">' +
                        '<i class="fas ' + (isSelected ? 'fa-check-circle' : 'fa-circle') + '"></i>' +
                    '</div>' +
                    '<div class="activity-icon"><i class="fas ' + icon + '"></i></div>' +
                    '<div class="activity-info">' +
                        '<div class="activity-name">' + esc(a.name) + '</div>' +
                        '<div class="activity-meta">' +
                            '<span><i class="fas fa-clock"></i> ' + (a.duration_hours || 2) + 'h</span>' +
                            '<span class="activity-category">' + (a.category || 'activity') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="activity-cost">' +
                        (a.cost_usd > 0 ? fmt(a.cost_usd) : '<span class="free-badge">Free</span>') +
                    '</div>' +
                '</div>';
            }).join('') +
        '</div>';

        updateActivitiesTotal();
    }

    // ── Toggle activity ───────────────────────────────────────────────────
    window.toggleActivity = function (index) {
        if (selectedActivities.has(index)) {
            selectedActivities.delete(index);
        } else {
            selectedActivities.add(index);
        }

        const el = document.getElementById('activity-' + index);
        if (el) {
            el.classList.toggle('selected', selectedActivities.has(index));
            const icon = el.querySelector('.activity-toggle i');
            if (icon) icon.className = 'fas ' + (selectedActivities.has(index) ? 'fa-check-circle' : 'fa-circle');
        }

        updateActivitiesTotal();
        renderCostBreakdown(); // Re-render full breakdown with new total
    };

    function calcActivitiesCost() {
        if (!costData || !costData.activities) return 0;
        let total = 0;
        costData.activities.forEach(function (a, i) {
            if (selectedActivities.has(i)) total += (a.cost_usd || 0);
        });
        return total;
    }

    function updateActivitiesTotal() {
        const totalRow = document.getElementById('activitiesTotalRow');
        const totalEl  = document.getElementById('activitiesTotal');
        if (!totalRow || !totalEl) return;
        const total = calcActivitiesCost();
        totalEl.textContent = fmt(total);
        totalRow.style.display = 'flex';
    }

    // ── Set cost tier ─────────────────────────────────────────────────────
    window.setCostTier = function (tier) {
        currentTier = tier;
        renderCostBreakdown();
    };

    // ── Currency change ───────────────────────────────────────────────────
    function tryRegister() {
        if (typeof window.Currency !== 'undefined') {
            window.Currency.onCurrencyChange(function () {
                window.Currency.refreshAllPrices();
                if (costData) { renderCostBreakdown(); renderActivities(); }
            });
            window.Currency.refreshAllPrices();
        } else {
            setTimeout(tryRegister, 100);
        }
    }
    tryRegister();

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Auto-load on page ready ───────────────────────────────────────────
    if (document.readyState !== 'loading') window.loadCostBreakdown();
    else document.addEventListener('DOMContentLoaded', window.loadCostBreakdown);

    // ── Load destination news ─────────────────────────────────────────────
    function loadDestinationNews() {
        const newsContent = document.getElementById('newsContent');
        if (!newsContent || !DEST) return;

        fetch('/api/destination-news?destination=' + encodeURIComponent(DEST) + '&country=' + encodeURIComponent(COUNTRY), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            const articles = res.articles || [];
            if (!articles.length) {
                newsContent.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);">' +
                    '<i class="fas fa-newspaper" style="font-size:32px;opacity:0.3;"></i>' +
                    '<p style="margin-top:12px;">No recent news available for this destination.</p>' +
                '</div>';
                return;
            }

            newsContent.innerHTML = '<div style="display:grid;gap:16px;">' +
                articles.map(function (article) {
                    const date = article.publishedAt ? new Date(article.publishedAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
                    const source = article.source?.name || 'News Source';
                    const img = article.image || 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&q=80';
                    
                    return '<a href="' + esc(article.url) + '" target="_blank" rel="noopener noreferrer" class="news-article" style="display:flex;gap:16px;padding:16px;border:1px solid var(--border);border-radius:6px;text-decoration:none;transition:all 0.3s;background:#fff;">' +
                        '<div style="width:120px;height:80px;flex-shrink:0;border-radius:4px;background:url(\'' + esc(img) + '\') center/cover;"></div>' +
                        '<div style="flex:1;min-width:0;">' +
                            '<h4 style="margin:0 0 6px;font-size:15px;color:var(--deep);line-height:1.4;">' + esc(article.title) + '</h4>' +
                            '<p style="margin:0 0 8px;font-size:13px;color:var(--text-muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' + esc(article.description || '') + '</p>' +
                            '<div style="display:flex;gap:12px;font-size:12px;color:var(--text-muted);">' +
                                '<span><i class="fas fa-newspaper"></i> ' + esc(source) + '</span>' +
                                (date ? '<span><i class="fas fa-calendar"></i> ' + date + '</span>' : '') +
                            '</div>' +
                        '</div>' +
                        '<div style="display:flex;align-items:center;color:var(--gold);">' +
                            '<i class="fas fa-external-link-alt"></i>' +
                        '</div>' +
                    '</a>';
                }).join('') +
            '</div>';

            // Add hover effect via CSS
            const style = document.createElement('style');
            style.textContent = '.news-article:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59,31,43,0.1); border-color: var(--gold); }';
            document.head.appendChild(style);
        })
        .catch(function (err) {
            newsContent.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);">' +
                '<i class="fas fa-exclamation-circle" style="font-size:24px;"></i>' +
                '<p style="margin-top:12px;">Could not load news. Please try again later.</p>' +
            '</div>';
        });
    }

    // Load news on page ready
    if (document.readyState !== 'loading') loadDestinationNews();
    else document.addEventListener('DOMContentLoaded', loadDestinationNews);

    // ── Load destination news ─────────────────────────────────────────────
    function loadDestinationNews() {
        const newsContent = document.getElementById('newsContent');
        if (!newsContent || !DEST) return;

        const url = '/api/destination-news?destination=' + encodeURIComponent(DEST) + '&country=' + encodeURIComponent(COUNTRY);
        
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                const articles = res.articles || [];
                
                if (!articles.length) {
                    newsContent.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);">' +
                        '<i class="fas fa-newspaper" style="font-size:32px;opacity:0.3;"></i>' +
                        '<p style="margin-top:12px;">No recent news available for this destination.</p>' +
                    '</div>';
                    return;
                }

                newsContent.innerHTML = '<div class="news-grid">' +
                    articles.map(function (article) {
                        const date = article.publishedAt ? new Date(article.publishedAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
                        const source = article.source && article.source.name ? article.source.name : 'News Source';
                        const img = article.image || 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&q=80';
                        
                        return '<a href="' + esc(article.url) + '" target="_blank" rel="noopener noreferrer" class="news-article">' +
                            '<div class="news-image" style="background-image:url(\'' + esc(img) + '\')"></div>' +
                            '<div class="news-content">' +
                                '<div class="news-meta">' +
                                    '<span class="news-source"><i class="fas fa-newspaper"></i> ' + esc(source) + '</span>' +
                                    (date ? '<span class="news-date"><i class="fas fa-clock"></i> ' + date + '</span>' : '') +
                                '</div>' +
                                '<h3 class="news-title">' + esc(article.title) + '</h3>' +
                                (article.description ? '<p class="news-description">' + esc(article.description.substring(0, 120)) + (article.description.length > 120 ? '...' : '') + '</p>' : '') +
                                '<span class="news-read-more">Read full article <i class="fas fa-external-link-alt"></i></span>' +
                            '</div>' +
                        '</a>';
                    }).join('') +
                '</div>';
            })
            .catch(function (err) {
                newsContent.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);">' +
                    '<i class="fas fa-exclamation-circle" style="font-size:32px;color:var(--danger);"></i>' +
                    '<p style="margin-top:12px;">Could not load news. Please try again later.</p>' +
                '</div>';
            });
    }

    // Load news on page ready
    if (document.readyState !== 'loading') loadDestinationNews();
    else document.addEventListener('DOMContentLoaded', loadDestinationNews);

    // ── Wishlist ──────────────────────────────────────────────────────────
    if (window.__isAuthenticated && DEST_ID) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const btn = document.getElementById('wishlistBtn');
        let isSaved = false;

        fetch('/api/wishlist/count', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.ids && data.ids.includes(DEST_ID)) {
                    isSaved = true;
                    if (btn) btn.innerHTML = '<i class="fas fa-heart" style="color:var(--danger)"></i> Saved';
                }
            }).catch(() => {});

        window.toggleWishlist = function (id) {
            if (!btn) return;
            if (isSaved) {
                fetch('/wishlist/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } })
                    .then(() => { isSaved = false; btn.innerHTML = '<i class="fas fa-heart"></i> Save to Wishlist'; })
                    .catch(() => {});
            } else {
                fetch('/wishlist', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ destination_id: id }) })
                    .then(() => { isSaved = true; btn.innerHTML = '<i class="fas fa-heart" style="color:var(--danger)"></i> Saved'; })
                    .catch(() => {});
            }
        };
    }
})();
