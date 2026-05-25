(function(){let e=document.getElementById(`discoverSearchForm`),t=document.getElementById(`discoverSearchInput`),n=document.getElementById(`discoverRegionFilter`),r=document.getElementById(`discoverMoodFilter`),i=document.getElementById(`discoverSearchBtn`),a=document.getElementById(`discoverGrid`),o=document.getElementById(`discoverEmpty`),s=document.getElementById(`discoverEmptyText`),c=document.getElementById(`discoverClearBtn`),l=document.getElementById(`discoverResultsInfo`),u=document.getElementById(`discoverSectionHeader`);document.getElementById(`discoverMoodSection`);let d={Cultural:`landmark`,Foodie:`utensils`,Beach:`umbrella-beach`,Nature:`leaf`,Photography:`camera`,Romantic:`heart`,Relaxed:`spa`,"Eco-Travel":`leaf`,Adventurous:`hiking`};function f(e){return String(e||``).replace(/&/g,`&amp;`).replace(/</g,`&lt;`).replace(/>/g,`&gt;`).replace(/"/g,`&quot;`)}function p(e){i&&(i.querySelector(`.btn-text`).classList.toggle(`hidden`,e),i.querySelector(`.btn-spinner`).classList.toggle(`hidden`,!e),i.disabled=e)}function m(){a.classList.remove(`hidden`),o.classList.add(`hidden`)}function h(e){a.innerHTML=``,a.classList.add(`hidden`),o.classList.remove(`hidden`),e&&s&&(s.textContent=e)}function g(e){document.querySelectorAll(`.mood-category-card`).forEach(function(t){t.classList.toggle(`mood-active`,t.dataset.mood===e)})}function _(e,i,a,o){if(!e&&!i&&!a){l.classList.add(`hidden`),u.classList.remove(`hidden`),g(null);return}u.classList.add(`hidden`),l.classList.remove(`hidden`);let s=[];e&&s.push(`"${f(e)}"`),i&&s.push(`in <strong>${f(n.options[n.selectedIndex]?.text||i)}</strong>`),a&&s.push(`Mood: <strong>${f(a)}</strong>`),l.innerHTML=`
            <div class="results-info-row">
                <div>
                    <h2><i class="fas fa-search"></i> Search Results
                        <span class="results-count">(${o} found)</span>
                    </h2>
                    <p class="search-query">${s.join(` · `)}</p>
                </div>
                <button type="button" class="btn btn-outline-sm results-clear-btn" id="resultsClearBtn">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>`;let c=document.getElementById(`resultsClearBtn`);c&&c.addEventListener(`click`,function(){t&&(t.value=``),n&&(n.value=``),r&&(r.value=``),y(``,``,``)})}function v(e){let t=(e.tags||[]).slice(0,3).map(function(e){return`<span class="tag"><i class="fas fa-${f(d[e]||`compass`)}"></i> ${f(e)}</span>`}).join(``),n=e.price_from>0?`<span class="tag tag-price"><i class="fas fa-tag"></i> ${window.Currency===void 0?`$`+e.price_from:window.Currency.format(e.price_from)}</span>`:``,r=f(e.country||e.region||`Global`),i=`https://picsum.photos/seed/`+encodeURIComponent(e.name||`travel`)+`/800/560`;return`
        <article class="card destination-card">
            <div class="card-image">
                <img src="${f(e.image_url)}"
                     alt="${f(e.name)}"
                     loading="lazy"
                     data-fallback="${f(i)}"
                     class="card-img-fallback"
                >
                ${e.is_featured?`<span class="card-badge"><i class="fas fa-star"></i> Featured</span>`:``}
                <div class="card-location-pill">
                    <i class="fas fa-map-marker-alt"></i> ${r}
                </div>
            </div>
            <div class="card-content">
                <h3 class="card-title">${f(e.name)}</h3>
                <p class="card-description">${f(e.description||`A destination worth exploring.`)}</p>
                ${t||n?`<div class="card-tags">${n}${t}</div>`:``}
                <div class="card-footer destination-card-footer">
                    <a href="${f(e.detail_url||`#`)}" class="btn btn-outline-sm btn-card-action">
                        <i class="fas fa-info-circle"></i> Details
                    </a>
                    <a href="${f(e.plan_url||`#`)}" class="btn btn-primary btn-card-action">
                        <i class="fas fa-route"></i> Plan Trip
                    </a>
                </div>
            </div>
        </article>`}async function y(e,t,n){p(!0),a.innerHTML=`<div class="discover-loading"><i class="fas fa-spinner fa-spin"></i><p>Searching destinations…</p></div>`,m();try{let r=new URLSearchParams;e&&r.set(`q`,e),t&&r.set(`region`,t),n&&r.set(`mood`,n);let i=await(await fetch(`/api/discover?`+r.toString(),{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`}})).json(),o=i.destinations||[];if(_(e,t,n,o.length),i.resolved_query&&i.resolved_query!==e){let e=document.getElementById(`discoverResultsInfo`);if(e){let t=document.createElement(`p`);t.className=`search-query`,t.innerHTML=`<i class="fas fa-lightbulb"></i> Showing results for <strong>${f(i.resolved_query)}</strong>`,e.appendChild(t)}}if(!o.length){h(e||t||n?`No destinations found for your search. Try a different term, country, or mood.`:`No destinations loaded yet. Try searching for a city or country.`);return}m(),a.innerHTML=o.map(v).join(``),window.Currency!==void 0&&window.Currency.refresh()}catch{a.innerHTML=`
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle empty-state-icon"></i>
                    <h3 class="empty-state-title">Something went wrong</h3>
                    <p class="empty-state-text">Could not load destinations. Please try again.</p>
                </div>`}finally{p(!1)}}e&&e.addEventListener(`submit`,function(e){e.preventDefault(),y(t?t.value.trim():``,n?n.value:``,r?r.value:``)}),document.querySelectorAll(`.mood-category-card`).forEach(function(e){e.addEventListener(`click`,function(){let e=this.dataset.mood;r&&(r.value=e),t&&(t.value=``),n&&(n.value=``),g(e),y(``,``,e),a&&a.scrollIntoView({behavior:`smooth`,block:`start`})})}),document.querySelectorAll(`.mood-icon-wrap[data-mood-bg]`).forEach(function(e){e.style.setProperty(`--mood-bg`,e.dataset.moodBg||``),e.style.setProperty(`--mood-color`,e.dataset.moodColor||``)}),c&&c.addEventListener(`click`,function(){t&&(t.value=``),n&&(n.value=``),r&&(r.value=``),g(null),y(``,``,``)}),document.addEventListener(`currency:changed`,function(){window.Currency&&window.Currency.refresh()}),y(``,``,``)})();