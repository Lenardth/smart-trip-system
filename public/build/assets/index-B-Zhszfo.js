let l=null,f=[],w="",d=null,r,v,k,b,p,h,B,x,u,_,j,N,y,m,I,T,M,S,$;function F(t){document.readyState!=="loading"?t():document.addEventListener("DOMContentLoaded",t)}F(()=>{r=document.getElementById("searchInput"),v=document.getElementById("styleSelect"),k=document.getElementById("budgetSelect"),b=document.getElementById("reloadBtn"),p=document.getElementById("accommodationsGrid"),h=document.getElementById("emptyState"),B=document.getElementById("aiMatchPanel"),x=document.getElementById("aiMatchSummary"),u=document.getElementById("locationPanel"),_=document.getElementById("mapCityLabel"),j=document.getElementById("mapAccomCount"),N=document.getElementById("newsCityLabel"),y=document.getElementById("newsDateline"),m=document.getElementById("newsLoading"),I=document.getElementById("newsError"),T=document.getElementById("newsErrorMsg"),M=document.getElementById("newsFeed"),S=document.getElementById("newsEmpty"),$=document.getElementById("newsMoreLink"),b&&b.addEventListener("click",C),r&&r.addEventListener("keydown",t=>{t.key==="Enter"&&C()}),A()});async function C(){const t=r.value.trim();await A(),t&&t.length>=2?(u.style.display="grid",D(t),P(t)):(u.style.display="none",q(),H(),w="")}async function A(){const t=r?r.value.trim():"",e=v?v.value:"any",n=k?k.value:"any";p.innerHTML='<div class="grid-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>',h.style.display="none";try{const a=new URLSearchParams;t&&a.set("q",t),e!=="any"&&a.set("style",e),n!=="any"&&a.set("budget",n);const i=await(await fetch(`/api/accommodations?${a.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}})).json(),c=i.data||i.accommodations||i||[];R(c),G(c),j.textContent=`${c.length} result${c.length!==1?"s":""}`,i.ai_summary?(x.textContent=i.ai_summary,B.style.display="block"):B.style.display="none"}catch{p.innerHTML='<p class="grid-error"><i class="fas fa-exclamation-triangle"></i> Failed to load accommodations.</p>'}}function R(t){if(!t.length){p.innerHTML="",h.style.display="block";return}h.style.display="none",p.innerHTML=t.map(e=>{const n=Number(e.rating||0),a=n?"★".repeat(n)+"☆".repeat(Math.max(0,5-n)):"",o=(e.amenities||[]).slice(0,4).map(g=>`<span class="accom-amenity"><i class="fas fa-check"></i> ${s(g)}</span>`).join(""),i=e.distance_km?`<span class="accom-dist"><i class="fas fa-location-arrow"></i> ${s(e.distance_km)}</span>`:"",c=e.price_per_night??e.nightly_rate??"";return`
        <div class="accom-card" data-lat="${e.lat||""}" data-lng="${e.lng||""}" data-id="${s(String(e.id||""))}">
            <div class="accom-image" style="background-image: url('${s(e.image_url||e.image||"")}')">
                <span class="accom-badge">${s(O(e.style))}</span>
                ${i}
            </div>
            <div class="accom-body">
                <div class="accom-header">
                    <h3 class="accom-name">${s(e.name)}</h3>
                    ${a?`<span class="accom-stars">${a}</span>`:""}
                </div>
                <p class="accom-location">
                    <i class="fas fa-map-marker-alt"></i>
                    ${s(e.city||"")}${e.country?", "+s(e.country):""}
                </p>
                <p class="accom-desc">${s((e.description||"").replace(/[★☆]/g,"").trim())}</p>
                ${o?`<div class="accom-amenities">${o}</div>`:""}
                <div class="accom-meta">
                    <span class="accom-price">
                        ${c!==""?`<span class="price-from">from</span> $${s(c)}<span class="price-unit">/night</span>`:""}
                    </span>
                    <span class="accom-budget-tag">${s(W(e.budget_tier))}</span>
                </div>
                <div class="accom-actions">
                    <button class="primary-button" onclick="window.location.href='/bookings/create?accommodation_id=${s(String(e.id||""))}'">
                        <i class="fas fa-calendar-check"></i> Book Now
                    </button>
                    <button class="secondary-button" onclick="jumpToNews('${s(e.city||"")}')">
                        <i class="fas fa-newspaper"></i> Local News
                    </button>
                </div>
            </div>
        </div>`}).join("")}function U(){l||(l=L.map("accommodationsMap",{zoomControl:!0,scrollWheelZoom:!1}).setView([20,0],2),L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:"© OpenStreetMap",maxZoom:18}).addTo(l))}function q(){l&&(f.forEach(t=>t.remove()),f=[])}async function D(t){U(),q(),_.textContent=t;try{const n=await(await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(t)}&format=json&limit=1`)).json();if(n.length){const{lat:a,lon:o}=n[0];l.setView([parseFloat(a),parseFloat(o)],11);const i=L.marker([a,o]).addTo(l);f.push(i)}}catch{}}function G(t){l&&t.forEach(e=>{if(!e.lat||!e.lng)return;const n=e.price_per_night??e.nightly_rate??"",a=L.marker([e.lat,e.lng]).addTo(l).bindPopup(`<strong>${s(e.name)}</strong><br>${n?"$"+s(n):""}`);f.push(a)})}function H(){M.innerHTML="",S.style.display="none",I.style.display="none",m.style.display="none",$.style.display="none",y&&(y.textContent="")}async function P(t){if(t!==w){w=t,d&&d.abort(),d=new AbortController,H(),N.textContent=`${t} Dispatch`,y&&(y.textContent=new Date().toLocaleDateString("en-US",{weekday:"long",year:"numeric",month:"long",day:"numeric"})),m.style.display="flex";try{let e=await E(`${t} travel tourism`,d.signal);if(e.length||(e=await E(`${t} tourism`,d.signal)),e.length||(e=await E("travel tourism",d.signal)),m.style.display="none",!e.length){S.style.display="flex";return}V(e,t)}catch(e){if(e.name==="AbortError")return;m.style.display="none",z(e.message||"Could not load news.")}}}async function E(t,e=null){const n=new URL("/api/accommodation-news",window.location.origin);n.searchParams.set("q",t);const a=await fetch(n.toString(),{signal:e,headers:{Accept:"application/json"}}),o=await a.json();if(!a.ok)throw new Error(o.message||"Failed to load news.");return o.articles??[]}function V(t,e){M.innerHTML=t.map(n=>{var g;const a=new Date(n.publishedAt),o=isNaN(a.getTime())?"":a.toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric"}),c=[((g=n.source)==null?void 0:g.name)||"",o].filter(Boolean).join(" &mdash; ");return`
        <div class="news-item">
            <a class="news-item-title" href="${s(n.url)}" target="_blank" rel="noopener">${s(n.title)}</a>
            ${c?`<div class="news-item-meta">${c}</div>`:""}
            ${n.description?`<p class="news-item-desc">${s(n.description)}</p>`:""}
        </div>`}).join(""),$.href=`https://news.google.com/search?q=${encodeURIComponent(e+" travel")}`,$.style.display="inline-flex"}function z(t){T.textContent=t,I.style.display="flex"}window.jumpToNews=function(t){t&&(r.value=t,u.style.display="grid",w="",D(t),P(t),u.scrollIntoView({behavior:"smooth",block:"start"}))};function s(t){return String(t||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}function O(t){return t||"Stay"}function W(t){return t||""}
