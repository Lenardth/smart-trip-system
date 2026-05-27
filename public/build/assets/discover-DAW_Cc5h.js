(function(){let e=document.getElementById(`discoverSearchForm`),t=document.getElementById(`discoverSearchInput`),n=document.getElementById(`discoverRegionFilter`),r=document.getElementById(`discoverMoodFilter`),i=document.getElementById(`discoverSearchBtn`),a=document.getElementById(`discoverGrid`),o=document.getElementById(`discoverEmpty`),s=document.getElementById(`discoverEmptyText`),c=document.getElementById(`discoverClearBtn`),l=document.getElementById(`discoverResultsInfo`),u=document.getElementById(`discoverSectionHeader`),d=document.getElementById(`discoverMoodSection`),f=!1,p={Cultural:`landmark`,Foodie:`utensils`,Beach:`umbrella-beach`,Nature:`leaf`,Photography:`camera`,Romantic:`heart`,Relaxed:`spa`,"Eco-Travel":`leaf`,Adventurous:`hiking`};function m(e){return String(e||``).replace(/&/g,`&amp;`).replace(/</g,`&lt;`).replace(/>/g,`&gt;`).replace(/"/g,`&quot;`)}function h(e){i&&(i.querySelector(`.btn-text`).classList.toggle(`hidden`,e),i.querySelector(`.btn-spinner`).classList.toggle(`hidden`,!e),i.disabled=e)}function g(){a.classList.remove(`hidden`),o.classList.add(`hidden`)}function _(e){a.innerHTML=``,a.classList.add(`hidden`),o.classList.remove(`hidden`),e&&s&&(s.textContent=e)}function v(e){document.querySelectorAll(`.mood-category-card`).forEach(function(t){t.classList.toggle(`mood-active`,t.dataset.mood===e)})}function y(e,t,r,i){if(!e&&!t&&!r){l.classList.add(`hidden`),u.classList.remove(`hidden`),d&&d.classList.remove(`hidden`),v(null);return}u.classList.add(`hidden`),d&&d.classList.add(`hidden`),l.classList.remove(`hidden`);let a=[];e&&a.push(`"${m(e)}"`),t&&a.push(`in <strong>${m(n.options[n.selectedIndex]?.text||t)}</strong>`),r&&a.push(`Mood: <strong>${m(r)}</strong>`),l.innerHTML=`
            <div class="results-info-row">
                <div>
                    <h2><i class="fas fa-search"></i> Search Results
                        <span class="results-count">(${i} found)</span>
                    </h2>
                    <p class="search-query">${a.join(` · `)}</p>
                </div>
                <button type="button" class="btn btn-outline-sm results-clear-btn" id="resultsClearBtn">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>`;let o=document.getElementById(`resultsClearBtn`);o&&o.addEventListener(`click`,function(){S(),w(``,``,``)})}function b(e){e&&e.dispatchEvent(new Event(`change`,{bubbles:!0}))}function x(){return{q:t?t.value.trim():``,region:n?n.value:``,mood:r?r.value:``}}function S(){t&&(t.value=``),n&&(n.value=``),r&&(r.value=``),f=!0,b(n),b(r),f=!1,v(null)}function C(e){let t=(e.tags||[]).slice(0,3).map(function(e){return`<span class="tag"><i class="fas fa-${m(p[e]||`compass`)}"></i> ${m(e)}</span>`}).join(``),n=e.price_from>0?`<span class="tag tag-price"><i class="fas fa-tag"></i> ${window.Currency===void 0?`$`+e.price_from:window.Currency.format(e.price_from)}</span>`:``,r=m(e.country||e.region||`Global`),i=`https://picsum.photos/seed/`+encodeURIComponent(e.name||`travel`)+`/800/560`;return`
        <article class="card destination-card">
            <div class="card-image">
                <img src="${m(e.image_url)}"
                     alt="${m(e.name)}"
                     loading="lazy"
                     data-fallback="${m(i)}"
                     class="card-img-fallback"
                >
                ${e.is_featured?`<span class="card-badge"><i class="fas fa-star"></i> Featured</span>`:``}
                <div class="card-location-pill">
                    <i class="fas fa-map-marker-alt"></i> ${r}
                </div>
            </div>
            <div class="card-content">
                <h3 class="card-title">${m(e.name)}</h3>
                <p class="card-description">${m(e.description||`A destination worth exploring.`)}</p>
                ${t||n?`<div class="card-tags">${n}${t}</div>`:``}
                <div class="card-footer destination-card-footer">
                    <a href="${m(e.detail_url||`#`)}" class="btn btn-outline-sm btn-card-action">
                        <i class="fas fa-info-circle"></i> Details
                    </a>
                    <a href="${m(e.plan_url||`#`)}" class="btn btn-primary btn-card-action">
                        <i class="fas fa-route"></i> Plan Trip
                    </a>
                </div>
            </div>
        </article>`}async function w(e,t,n){h(!0),a.innerHTML=`<div class="discover-loading"><i class="fas fa-spinner fa-spin"></i><p>Searching destinations…</p></div>`,g();try{let r=new URLSearchParams;e&&r.set(`q`,e),t&&r.set(`region`,t),n&&r.set(`mood`,n);let i=await(await fetch(`/api/discover?`+r.toString(),{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`}})).json(),o=i.destinations||[];if(y(e,t,n,o.length),i.resolved_query&&i.resolved_query!==e){let e=document.getElementById(`discoverResultsInfo`);if(e){let t=document.createElement(`p`);t.className=`search-query`,t.innerHTML=`<i class="fas fa-lightbulb"></i> Showing results for <strong>${m(i.resolved_query)}</strong>`,e.appendChild(t)}}if(!o.length){_(e||t||n?`No destinations found for your search. Try a different term, country, or mood.`:`No destinations loaded yet. Try searching for a city or country.`);return}g(),a.innerHTML=o.map(C).join(``),window.Currency!==void 0&&window.Currency.refresh()}catch{a.innerHTML=`
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle empty-state-icon"></i>
                    <h3 class="empty-state-title">Something went wrong</h3>
                    <p class="empty-state-text">Could not load destinations. Please try again.</p>
                </div>`}finally{h(!1)}}e&&e.addEventListener(`submit`,function(e){e.preventDefault();let t=x();w(t.q,t.region,t.mood)}),[n,r].forEach(function(e){e&&e.addEventListener(`change`,function(){if(f)return;let e=x();v(e.mood),w(e.q,e.region,e.mood)})}),document.querySelectorAll(`.mood-category-card`).forEach(function(e){e.addEventListener(`click`,function(){let e=this.dataset.mood;r&&(r.value=e),t&&(t.value=``),n&&(n.value=``),f=!0,b(r),b(n),f=!1,v(e),w(``,``,e),a&&a.scrollIntoView({behavior:`smooth`,block:`start`})})}),document.querySelectorAll(`.mood-icon-wrap[data-mood-bg]`).forEach(function(e){e.style.setProperty(`--mood-bg`,e.dataset.moodBg||``),e.style.setProperty(`--mood-color`,e.dataset.moodColor||``)}),c&&c.addEventListener(`click`,function(){S(),w(``,``,``)}),document.addEventListener(`currency:changed`,function(){window.Currency&&window.Currency.refresh()}),w(``,``,``)})();