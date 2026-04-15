const c={};function v(s){const i={safe:{icon:"fa-shield-alt",color:"#2e7d32",bg:"#e8f5e9",label:"Generally Safe"},caution:{icon:"fa-exclamation-triangle",color:"#e65100",bg:"#fff3e0",label:"Exercise Caution"},avoid:{icon:"fa-times-circle",color:"#b71c1c",bg:"#ffebee",label:"Avoid if Possible"}},e=i[s]||i.caution;return`<span class="advisory-badge" style="background:${e.bg};color:${e.color};border:1px solid ${e.color}33;">
        <i class="fas ${e.icon}"></i> ${e.label}
    </span>`}function r(s){return!s||!s.length?'<li style="color:var(--text-muted)">No data available.</li>':s.map(i=>`<li>${a(i)}</li>`).join("")}function a(s){return String(s||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")}function n(s){const i=s.best_time_now?'<span class="advisory-time-badge good"><i class="fas fa-sun"></i> Good time to visit</span>':'<span class="advisory-time-badge bad"><i class="fas fa-cloud-rain"></i> Not ideal right now</span>';return`
    <div class="travel-advisory-panel">
        <div class="advisory-header">
            <div class="advisory-title-row">
                <i class="fas fa-satellite-dish"></i>
                <span>Live Travel Advisory</span>
                <span class="advisory-dest-name">${a(s.destination)}, ${a(s.country)}</span>
            </div>
            <div class="advisory-badges-row">
                ${v(s.safety_level)}
                ${i}
            </div>
        </div>

        <div class="advisory-body">
            <div class="advisory-section advisory-section-highlight">
                <h4><i class="fas fa-shield-alt"></i> Safety Overview</h4>
                <p>${a(s.safety_summary)}</p>
            </div>

            <div class="advisory-section">
                <h4><i class="fas fa-newspaper"></i> Current Affairs</h4>
                <p>${a(s.current_affairs)}</p>
            </div>

            <div class="advisory-grid-2">
                <div class="advisory-section">
                    <h4><i class="fas fa-thumbs-up" style="color:#2e7d32"></i> Best Areas to Visit</h4>
                    <ul>${r(s.best_areas)}</ul>
                </div>
                <div class="advisory-section">
                    <h4><i class="fas fa-thumbs-down" style="color:#b71c1c"></i> Areas / Situations to Avoid</h4>
                    <ul>${r(s.areas_to_avoid)}</ul>
                </div>
            </div>

            <div class="advisory-section">
                <h4><i class="fas fa-bed"></i> Best Areas to Stay</h4>
                <ul>${r(s.best_accommodation_areas)}</ul>
            </div>

            <div class="advisory-section">
                <h4><i class="fas fa-lightbulb" style="color:var(--gold)"></i> Insider Tips</h4>
                <ul>${r(s.top_tips)}</ul>
            </div>

            <div class="advisory-grid-2">
                <div class="advisory-section">
                    <h4><i class="fas fa-bus"></i> Getting Around</h4>
                    <p>${a(s.local_transport)}</p>
                </div>
                <div class="advisory-section">
                    <h4><i class="fas fa-money-bill-wave"></i> Money & Payments</h4>
                    <p>${a(s.money_tips)}</p>
                </div>
            </div>
        </div>

        <div class="advisory-footer">
            <i class="fas fa-robot"></i> AI-generated advisory &mdash; updated hourly &mdash;
            <span style="opacity:.6;font-size:11px;">${new Date(s.generated_at).toLocaleString()}</span>
        </div>
    </div>`}function y(s){return`
    <div class="travel-advisory-panel advisory-loading">
        <div class="advisory-header">
            <div class="advisory-title-row">
                <i class="fas fa-satellite-dish fa-pulse"></i>
                <span>Fetching live advisory for <strong>${a(s)}</strong>…</span>
            </div>
        </div>
        <div class="advisory-skeleton">
            <div class="adv-skel-line" style="width:60%"></div>
            <div class="adv-skel-line" style="width:80%"></div>
            <div class="adv-skel-line" style="width:50%"></div>
            <div class="adv-skel-line" style="width:70%"></div>
        </div>
    </div>`}function f(s){return`
    <div class="travel-advisory-panel advisory-error">
        <div class="advisory-header">
            <div class="advisory-title-row">
                <i class="fas fa-exclamation-circle" style="color:#b71c1c"></i>
                <span>Could not load advisory</span>
            </div>
        </div>
        <p style="padding:16px 20px;color:var(--text-muted);font-size:13.5px;">${a(s)}</p>
    </div>`}window.renderTravelAdvisory=async function(s,i,e){const o=document.getElementById(s);if(!o)return;const d=(i+"|"+(e||"")).toLowerCase();if(c[d]){o.innerHTML=n(c[d]),o.style.display="block";return}o.innerHTML=y(i),o.style.display="block",o.scrollIntoView({behavior:"smooth",block:"nearest"});try{const t=new URLSearchParams({destination:i,country:e||i}),l=await(await fetch(`/api/travel-advisory?${t}`,{headers:{Accept:"application/json"}})).json();if(!l.success)throw new Error(l.message||"Advisory unavailable");c[d]=l.data,o.innerHTML=n(l.data)}catch(t){o.innerHTML=f(t.message||"Could not load advisory.")}};
