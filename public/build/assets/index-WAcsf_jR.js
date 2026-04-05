let l=null,g=[],f="",d=null,r,E,v,$,p,w,k,C,u,x,_,j,y,m,B,N,I,M,h;function F(t){document.readyState!=="loading"?t():document.addEventListener("DOMContentLoaded",t)}F(()=>{r=document.getElementById("searchInput"),E=document.getElementById("styleSelect"),v=document.getElementById("budgetSelect"),$=document.getElementById("reloadBtn"),p=document.getElementById("accommodationsGrid"),w=document.getElementById("emptyState"),k=document.getElementById("aiMatchPanel"),C=document.getElementById("aiMatchSummary"),u=document.getElementById("locationPanel"),x=document.getElementById("mapCityLabel"),_=document.getElementById("mapAccomCount"),j=document.getElementById("newsCityLabel"),y=document.getElementById("newsDateline"),m=document.getElementById("newsLoading"),B=document.getElementById("newsError"),N=document.getElementById("newsErrorMsg"),I=document.getElementById("newsFeed"),M=document.getElementById("newsEmpty"),h=document.getElementById("newsMoreLink"),$&&$.addEventListener("click",S),r&&r.addEventListener("keydown",t=>{t.key==="Enter"&&S()}),T()});async function S(){const t=r.value.trim();await T(),t&&t.length>=2?(u.style.display="grid",q(t),H(t)):(u.style.display="none",A(),D(),f="")}async function T(){const t=r?r.value.trim():"",e=E?E.value:"any",n=v?v.value:"any";p.innerHTML='<div class="grid-loading"><i class="fas fa-spinner fa-spin"></i> Loading…</div>',w.style.display="none";try{const a=new URLSearchParams;t&&a.set("q",t),e!=="any"&&a.set("style",e),n!=="any"&&a.set("budget",n);const i=await(await fetch(`/api/accommodations?${a.toString()}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}})).json(),c=i.data||i.accommodations||i||[];R(c),G(c),_.textContent=`${c.length} result${c.length!==1?"s":""}`,i.ai_summary?(C.textContent=i.ai_summary,k.style.display="block"):k.style.display="none"}catch{p.innerHTML='<p class="grid-error"><i class="fas fa-exclamation-triangle"></i> Failed to load accommodations.</p>'}}function R(t){if(!t.length){p.innerHTML="",w.style.display="block";return}w.style.display="none",p.innerHTML=t.map(e=>{const n=Number(e.rating||0),a=n?"★".repeat(n)+"☆".repeat(Math.max(0,5-n)):"",o=(e.amenities||[]).slice(0,4).map(P=>`<span class="accom-amenity"><i class="fas fa-check"></i> ${s(P)}</span>`).join(""),i=e.distance_km?`<span class="accom-dist"><i class="fas fa-location-arrow"></i> ${s(e.distance_km)}</span>`:"",c=e.price_per_night??e.nightly_rate??"";return`
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
        </div>`}).join("")}function U(){l||(l=L.map("accommodationsMap",{zoomControl:!0,scrollWheelZoom:!1}).setView([20,0],2),L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:"© OpenStreetMap",maxZoom:18}).addTo(l))}function A(){l&&(g.forEach(t=>t.remove()),g=[])}async function q(t){U(),A(),x.textContent=t;try{const n=await(await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(t)}&format=json&limit=1`)).json();if(n.length){const{lat:a,lon:o}=n[0];l.setView([parseFloat(a),parseFloat(o)],11);const i=L.marker([a,o]).addTo(l);g.push(i)}}catch{}}function G(t){l&&t.forEach(e=>{if(!e.lat||!e.lng)return;const n=e.price_per_night??e.nightly_rate??"",a=L.marker([e.lat,e.lng]).addTo(l).bindPopup(`<strong>${s(e.name)}</strong><br>${n?"$"+s(n):""}`);g.push(a)})}function D(){I.innerHTML="",M.style.display="none",B.style.display="none",m.style.display="none",h.style.display="none",y&&(y.textContent="")}async function H(t){if(t!==f){f=t,d&&d.abort(),d=new AbortController,D(),j.textContent=`${t} Dispatch`,y&&(y.textContent=new Date().toLocaleDateString("en-US",{weekday:"long",year:"numeric",month:"long",day:"numeric"})),m.style.display="flex";try{let e=await b(`${t} travel tourism`,d.signal);if(e.length||(e=await b(`${t} tourism`,d.signal)),e.length||(e=await b("travel tourism",d.signal)),m.style.display="none",!e.length){M.style.display="flex";return}V(e,t)}catch(e){if(e.name==="AbortError")return;m.style.display="none",z(e.message||"Could not load news.")}}}async function b(t,e=null){const n=new URL("/api/accommodation-news",window.location.origin);n.searchParams.set("q",t);const a=await fetch(n.toString(),{signal:e,headers:{Accept:"application/json"}}),o=await a.json();if(!a.ok)throw new Error(o.message||"Failed to load news.");return o.articles??[]}function V(t,e){I.innerHTML=t.map(n=>{const a=new Date(n.publishedAt),o=isNaN(a.getTime())?"":a.toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric"}),c=[n.source?.name||"",o].filter(Boolean).join(" &mdash; ");return`
        <div class="news-item">
            <a class="news-item-title" href="${s(n.url)}" target="_blank" rel="noopener">${s(n.title)}</a>
            ${c?`<div class="news-item-meta">${c}</div>`:""}
            ${n.description?`<p class="news-item-desc">${s(n.description)}</p>`:""}
        </div>`}).join(""),h.href=`https://news.google.com/search?q=${encodeURIComponent(e+" travel")}`,h.style.display="inline-flex"}function z(t){N.textContent=t,B.style.display="flex"}window.jumpToNews=function(t){t&&(r.value=t,u.style.display="grid",f="",q(t),H(t),u.scrollIntoView({behavior:"smooth",block:"start"}))};function s(t){return String(t||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}function O(t){return t||"Stay"}function W(t){return t||""}
