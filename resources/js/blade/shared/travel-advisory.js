/**
 * Travel Advisory Panel — shared between plan-trip and accommodations pages.
 * Usage: renderTravelAdvisory(containerId, destination, country)
 */

const advisoryCache = {};

function safetyBadge(level) {
    const map = {
        safe:    { icon: 'fa-shield-alt',       color: '#2e7d32', bg: '#e8f5e9', label: 'Generally Safe' },
        caution: { icon: 'fa-exclamation-triangle', color: '#e65100', bg: '#fff3e0', label: 'Exercise Caution' },
        avoid:   { icon: 'fa-times-circle',     color: '#b71c1c', bg: '#ffebee', label: 'Avoid if Possible' },
    };
    const s = map[level] || map.caution;
    return `<span class="advisory-badge" style="background:${s.bg};color:${s.color};border:1px solid ${s.color}33;">
        <i class="fas ${s.icon}"></i> ${s.label}
    </span>`;
}

function listItems(arr) {
    if (!arr || !arr.length) return '<li style="color:var(--text-muted)">No data available.</li>';
    return arr.map(item => `<li>${escAdv(item)}</li>`).join('');
}

function escAdv(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function buildAdvisoryHTML(data) {
    const timeBadge = data.best_time_now
        ? `<span class="advisory-time-badge good"><i class="fas fa-sun"></i> Good time to visit</span>`
        : `<span class="advisory-time-badge bad"><i class="fas fa-cloud-rain"></i> Not ideal right now</span>`;

    return `
    <div class="travel-advisory-panel">
        <div class="advisory-header">
            <div class="advisory-title-row">
                <i class="fas fa-satellite-dish"></i>
                <span>Live Travel Advisory</span>
                <span class="advisory-dest-name">${escAdv(data.destination)}, ${escAdv(data.country)}</span>
            </div>
            <div class="advisory-badges-row">
                ${safetyBadge(data.safety_level)}
                ${timeBadge}
            </div>
        </div>

        <div class="advisory-body">
            <div class="advisory-section advisory-section-highlight">
                <h4><i class="fas fa-shield-alt"></i> Safety Overview</h4>
                <p>${escAdv(data.safety_summary)}</p>
            </div>

            <div class="advisory-section">
                <h4><i class="fas fa-newspaper"></i> Current Affairs</h4>
                <p>${escAdv(data.current_affairs)}</p>
            </div>

            <div class="advisory-grid-2">
                <div class="advisory-section">
                    <h4><i class="fas fa-thumbs-up" style="color:#2e7d32"></i> Best Areas to Visit</h4>
                    <ul>${listItems(data.best_areas)}</ul>
                </div>
                <div class="advisory-section">
                    <h4><i class="fas fa-thumbs-down" style="color:#b71c1c"></i> Areas / Situations to Avoid</h4>
                    <ul>${listItems(data.areas_to_avoid)}</ul>
                </div>
            </div>

            <div class="advisory-section">
                <h4><i class="fas fa-bed"></i> Best Areas to Stay</h4>
                <ul>${listItems(data.best_accommodation_areas)}</ul>
            </div>

            <div class="advisory-section">
                <h4><i class="fas fa-lightbulb" style="color:var(--gold)"></i> Insider Tips</h4>
                <ul>${listItems(data.top_tips)}</ul>
            </div>

            <div class="advisory-grid-2">
                <div class="advisory-section">
                    <h4><i class="fas fa-bus"></i> Getting Around</h4>
                    <p>${escAdv(data.local_transport)}</p>
                </div>
                <div class="advisory-section">
                    <h4><i class="fas fa-money-bill-wave"></i> Money & Payments</h4>
                    <p>${escAdv(data.money_tips)}</p>
                </div>
            </div>
        </div>

        <div class="advisory-footer">
            <i class="fas fa-robot"></i> AI-generated advisory &mdash; updated hourly &mdash;
            <span style="opacity:.6;font-size:11px;">${new Date(data.generated_at).toLocaleString()}</span>
        </div>
    </div>`;
}

function buildAdvisoryLoadingHTML(destination) {
    return `
    <div class="travel-advisory-panel advisory-loading">
        <div class="advisory-header">
            <div class="advisory-title-row">
                <i class="fas fa-satellite-dish fa-pulse"></i>
                <span>Fetching live advisory for <strong>${escAdv(destination)}</strong>…</span>
            </div>
        </div>
        <div class="advisory-skeleton">
            <div class="adv-skel-line" style="width:60%"></div>
            <div class="adv-skel-line" style="width:80%"></div>
            <div class="adv-skel-line" style="width:50%"></div>
            <div class="adv-skel-line" style="width:70%"></div>
        </div>
    </div>`;
}

function buildAdvisoryErrorHTML(msg) {
    return `
    <div class="travel-advisory-panel advisory-error">
        <div class="advisory-header">
            <div class="advisory-title-row">
                <i class="fas fa-exclamation-circle" style="color:#b71c1c"></i>
                <span>Could not load advisory</span>
            </div>
        </div>
        <p style="padding:16px 20px;color:var(--text-muted);font-size:13.5px;">${escAdv(msg)}</p>
    </div>`;
}

window.renderTravelAdvisory = async function(containerId, destination, country) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const cacheKey = (destination + '|' + (country || '')).toLowerCase();
    if (advisoryCache[cacheKey]) {
        container.innerHTML = buildAdvisoryHTML(advisoryCache[cacheKey]);
        container.style.display = 'block';
        return;
    }

    container.innerHTML = buildAdvisoryLoadingHTML(destination);
    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    try {
        const params = new URLSearchParams({ destination, country: country || destination });
        const res  = await fetch(`/api/travel-advisory?${params}`, {
            headers: { 'Accept': 'application/json' },
        });
        const json = await res.json();

        if (!json.success) throw new Error(json.message || 'Advisory unavailable');

        advisoryCache[cacheKey] = json.data;
        container.innerHTML = buildAdvisoryHTML(json.data);
    } catch (err) {
        container.innerHTML = buildAdvisoryErrorHTML(err.message || 'Could not load advisory.');
    }
};
