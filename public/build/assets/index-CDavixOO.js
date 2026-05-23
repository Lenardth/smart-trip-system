(function(){const v=document.getElementById("discoverSearchForm"),o=document.getElementById("discoverSearchInput"),a=document.getElementById("discoverRegionFilter"),r=document.getElementById("discoverMoodFilter"),m=document.getElementById("discoverSearchBtn"),l=document.getElementById("discoverGrid"),y=document.getElementById("discoverEmpty"),b=document.getElementById("discoverEmptyText"),E=document.getElementById("discoverClearBtn"),p=document.getElementById("discoverResultsInfo"),L=document.getElementById("discoverSectionHeader");document.getElementById("discoverMoodSection");const B={Cultural:"landmark",Foodie:"utensils",Beach:"umbrella-beach",Nature:"leaf",Photography:"camera",Romantic:"heart",Relaxed:"spa","Eco-Travel":"leaf",Adventurous:"hiking"};function s(e){return String(e||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}function I(e){m&&(m.querySelector(".btn-text").classList.toggle("hidden",e),m.querySelector(".btn-spinner").classList.toggle("hidden",!e),m.disabled=e)}function w(){l.classList.remove("hidden"),y.classList.add("hidden")}function S(e){l.innerHTML="",l.classList.add("hidden"),y.classList.remove("hidden"),b&&(b.textContent=e)}function g(e){document.querySelectorAll(".mood-category-card").forEach(function(t){t.classList.toggle("mood-active",t.dataset.mood===e)})}function C(e,t,n,c){var d;if(!e&&!t&&!n){p.classList.add("hidden"),L.classList.remove("hidden"),g(null);return}L.classList.add("hidden"),p.classList.remove("hidden");const u=[];e&&u.push(`"${s(e)}"`),t&&u.push(`in <strong>${s(((d=a.options[a.selectedIndex])==null?void 0:d.text)||t)}</strong>`),n&&u.push(`Mood: <strong>${s(n)}</strong>`),p.innerHTML=`
            <div class="results-info-row">
                <div>
                    <h2><i class="fas fa-search"></i> Search Results
                        <span class="results-count">(${c} found)</span>
                    </h2>
                    <p class="search-query">${u.join(" · ")}</p>
                </div>
                <button type="button" class="btn btn-outline-sm results-clear-btn" id="resultsClearBtn">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>`;const i=document.getElementById("resultsClearBtn");i&&i.addEventListener("click",function(){o&&(o.value=""),a&&(a.value=""),r&&(r.value=""),f("","","")})}function T(e){const t=(e.tags||[]).slice(0,3).map(function(i){const d=B[i]||"compass";return`<span class="tag"><i class="fas fa-${s(d)}"></i> ${s(i)}</span>`}).join(""),n=e.price_from>0?`<span class="tag tag-price"><i class="fas fa-tag"></i> ${typeof window.Currency<"u"?window.Currency.format(e.price_from):"$"+e.price_from}</span>`:"",c=s(e.country||e.region||"Global"),u="https://picsum.photos/seed/"+encodeURIComponent(e.name||"travel")+"/800/560";return`
        <article class="card destination-card">
            <div class="card-image">
                <img src="${s(e.image_url)}"
                     alt="${s(e.name)}"
                     loading="lazy"
                     data-fallback="${s(u)}"
                     class="card-img-fallback"
                >
                ${e.is_featured?'<span class="card-badge"><i class="fas fa-star"></i> Featured</span>':""}
                <div class="card-location-pill">
                    <i class="fas fa-map-marker-alt"></i> ${c}
                </div>
            </div>
            <div class="card-content">
                <h3 class="card-title">${s(e.name)}</h3>
                <p class="card-description">${s(e.description||"A destination worth exploring.")}</p>
                ${t||n?`<div class="card-tags">${n}${t}</div>`:""}
                <div class="card-footer destination-card-footer">
                    <a href="${s(e.detail_url||"#")}" class="btn btn-outline-sm btn-card-action">
                        <i class="fas fa-info-circle"></i> Details
                    </a>
                    <a href="${s(e.plan_url||"#")}" class="btn btn-primary btn-card-action">
                        <i class="fas fa-route"></i> Plan Trip
                    </a>
                </div>
            </div>
        </article>`}async function f(e,t,n){I(!0),l.innerHTML='<div class="discover-loading"><i class="fas fa-spinner fa-spin"></i><p>Searching destinations…</p></div>',w();try{const c=new URLSearchParams;e&&c.set("q",e),t&&c.set("region",t),n&&c.set("mood",n);const i=await(await fetch("/api/discover?"+c.toString(),{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}})).json(),d=i.destinations||[];if(C(e,t,n,d.length),i.resolved_query&&i.resolved_query!==e){const $=document.getElementById("discoverResultsInfo");if($){const h=document.createElement("p");h.className="search-query",h.innerHTML=`<i class="fas fa-lightbulb"></i> Showing results for <strong>${s(i.resolved_query)}</strong>`,$.appendChild(h)}}if(!d.length){S(e||t||n?"No destinations found for your search. Try a different term, country, or mood.":"No destinations loaded yet. Try searching for a city or country.");return}w(),l.innerHTML=d.map(T).join(""),typeof window.Currency<"u"&&window.Currency.refresh()}catch{l.innerHTML=`
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle empty-state-icon"></i>
                    <h3 class="empty-state-title">Something went wrong</h3>
                    <p class="empty-state-text">Could not load destinations. Please try again.</p>
                </div>`}finally{I(!1)}}v&&v.addEventListener("submit",function(e){e.preventDefault(),f(o?o.value.trim():"",a?a.value:"",r?r.value:"")}),document.querySelectorAll(".mood-category-card").forEach(function(e){e.addEventListener("click",function(){const t=this.dataset.mood;r&&(r.value=t),o&&(o.value=""),a&&(a.value=""),g(t),f("","",t),l&&l.scrollIntoView({behavior:"smooth",block:"start"})})}),E&&E.addEventListener("click",function(){o&&(o.value=""),a&&(a.value=""),r&&(r.value=""),g(null),f("","","")}),document.addEventListener("currency:changed",function(){window.Currency&&window.Currency.refresh()}),f("","","")})();
