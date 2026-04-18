const I=window.__dashboardConfig||{};var b;const k=((b=document.querySelector('meta[name="csrf-token"]'))==null?void 0:b.content)??"",L=!!I.userId,_={relaxed:"fa-spa",adventurous:"fa-mountain",cultural:"fa-landmark",romantic:"fa-heart",foodie:"fa-utensils",eco:"fa-leaf",eco_tourism:"fa-leaf",beach:"fa-umbrella-beach",mountain:"fa-mountain",historical:"fa-landmark",food_culture:"fa-utensils",nature:"fa-tree",general:"fa-globe"},o={category:"all",region:"all",query:"",debounceTimer:null};let r=new Set;function u(e,t){t=t||"fa-info-circle";const i=document.getElementById("toast");if(!i)return;const n=i.querySelector("i");n&&(n.className="fas "+t);const s=document.getElementById("toastMsg");s&&(s.textContent=e),i.classList.add("show"),setTimeout(()=>i.classList.remove("show"),3e3)}function c(e,t){t=t||{};const i=Object.assign({Accept:"application/json","X-CSRF-TOKEN":k},t.headers||{});return fetch(e,Object.assign({},t,{headers:i,credentials:"same-origin"})).then(n=>{if(!n.ok)throw new Error("HTTP "+n.status);return n.json()})}function B(){const e=new URLSearchParams;return o.category!=="all"&&e.set("category",o.category),o.region!=="all"&&e.set("region",o.region),o.query.trim()&&e.set("q",o.query.trim()),e.toString()?"?"+e.toString():""}function M(){L&&c("/api/wishlist/count").then(e=>{r=new Set(e.ids??[]);const t=document.getElementById("wishlistCount");t&&(t.textContent=e.count??r.size)}).catch(()=>{})}function H(e,t){if(!L){window.location.href="/login";return}const i=r.has(e),n=t.querySelector("i");i?c("/wishlist/"+e,{method:"DELETE"}).then(()=>{r.delete(e),n&&(n.className="far fa-heart"),t.title="Save to Wishlist",u("Removed from wishlist","fa-heart-broken");try{localStorage.setItem("smartBookingWishlistUpdated",String(Date.now()))}catch{}window.__refreshWishlistBadge&&window.__refreshWishlistBadge()}).catch(()=>u("Could not remove","fa-exclamation-circle")):c("/wishlist",{method:"POST",headers:{Accept:"application/json","Content-Type":"application/json","X-CSRF-TOKEN":k},body:JSON.stringify({destination_id:e})}).then(()=>{r.add(e),n&&(n.className="fas fa-heart"),t.title="Remove from Wishlist",u("Saved to Wishlist!","fa-heart");try{localStorage.setItem("smartBookingWishlistUpdated",String(Date.now()))}catch{}window.__refreshWishlistBadge&&window.__refreshWishlistBadge()}).catch(()=>u("Could not save","fa-exclamation-circle"))}function C(e){const t=document.getElementById("destinationsGrid"),i=document.getElementById("resultsInfo");if(t){if(!e.length){t.innerHTML='<div class="empty-state"><i class="fas fa-search"></i><p>No destinations found. Try adjusting your filters.</p></div>',i&&(i.textContent="");return}i&&(i.textContent=e.length+" destination"+(e.length!==1?"s":"")+" found"),t.innerHTML=e.map(n=>{var v;const s=_[(v=n.mood)==null?void 0:v.toLowerCase()]||"fa-globe",a=n.mood?n.mood.replace(/_/g," ").replace(/\b\w/g,S=>S.toUpperCase()):"",m=n.badge?`<span class="dest-badge">${n.badge}</span>`:"",$=n.image_url?`background-image:url('${n.image_url}')`:"",h=r.has(n.id),x=n.price_from>0?'<span class="dest-price">'+(n.currency&&typeof window.Currency<"u"?window.Currency.symbol(n.currency):"$")+Math.round(n.price_from).toLocaleString()+" <span>/ person</span></span>":"";return`<div class="destination-card" data-id="${n.id}">
            <div class="destination-image" style="${$}">
                ${m}
                <button class="wishlist-toggle" data-id="${n.id}" title="${h?"Remove from Wishlist":"Save to Wishlist"}">
                    <i class="${h?"fas":"far"} fa-heart"></i>
                </button>
            </div>
            <div class="destination-content">
                <h3>${n.name}${n.country?", "+n.country:""}</h3>
                <div class="destination-meta">
                    ${a?`<span class="mood-indicator"><i class="fas ${s}"></i> ${a}</span>`:""}
                </div>
                <p>${n.description?n.description.substring(0,110)+(n.description.length>110?"…":""):""}</p>
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:auto;">
                    ${x}
                    <a href="/destination-info/${n.id}" class="primary-button" style="text-decoration:none;padding:8px 16px;font-size:13px;text-align:center;width:100%;">
                        View Details
                    </a>
                </div>
            </div>
        </div>`}).join(""),t.querySelectorAll(".wishlist-toggle").forEach(n=>{n.addEventListener("click",s=>{s.preventDefault(),s.stopPropagation(),H(Number(n.dataset.id),n)})})}}function T(e){return e=e||6,Array.from({length:e},()=>`<div class="destination-card">
            <div class="destination-image skeleton"></div>
            <div class="destination-content">
                <div class="sk-line medium skeleton"></div>
                <div class="sk-line short skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton" style="margin-top:10px;height:36px;border-radius:4px;"></div>
            </div>
        </div>`).join("")}function l(){const e=document.getElementById("destinationsGrid"),t=document.getElementById("resultsInfo");e&&(t&&(t.textContent=""),e.innerHTML=T(),c("/api/discover/destinations"+B()).then(i=>C(i.data??i)).catch(()=>{e.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not load destinations. Please try again.</p></div>'}))}function D(e){const t=document.getElementById("hiddenGemsGrid");if(t){if(!e.length){t.innerHTML='<p style="color:rgba(255,255,255,.6);grid-column:1/-1;text-align:center;">No hidden gems found.</p>';return}t.innerHTML=e.map(i=>{const n=i.image_url?`background-image:url('${i.image_url}')`:"";return`<a href="/destination-info/${i.id}" class="featured-card" style="text-decoration:none;display:block;">
            <div class="feat-img" style="${n}"></div>
            <div class="feat-body">
                <h4>${i.name}${i.country?", "+i.country:""}</h4>
                <p>${i.description?i.description.substring(0,80)+(i.description.length>80?"…":""):""}</p>
                ${i.match_score?`<span class="feat-tag">${i.match_score}% Match</span>`:""}
            </div>
        </a>`}).join("")}}function g(){const e=document.getElementById("hiddenGemsGrid");e&&(e.innerHTML=Array.from({length:3},()=>`<div class="featured-card">
            <div class="feat-img skeleton"></div>
            <div class="feat-body">
                <div class="sk-line medium skeleton" style="background:rgba(255,255,255,.15);"></div>
                <div class="sk-line full skeleton" style="background:rgba(255,255,255,.1);margin-top:6px;"></div>
            </div>
        </div>`).join(""),c("/api/discover/hidden-gems").then(t=>D(t.data??t)).catch(()=>{e&&(e.innerHTML="")}))}function j(){const e=document.getElementById("filterTabs");e&&e.addEventListener("click",t=>{const i=t.target.closest(".filter-tab");i&&(document.querySelectorAll(".filter-tab").forEach(n=>n.classList.remove("active")),i.classList.add("active"),o.category=i.dataset.category,l())})}function q(){const e=document.getElementById("regionRow");e&&e.addEventListener("click",t=>{const i=t.target.closest(".region-pill");i&&(document.querySelectorAll(".region-pill").forEach(n=>n.classList.remove("active")),i.classList.add("active"),o.region=i.dataset.region,l())})}function R(){const e=document.getElementById("searchInput"),t=document.getElementById("searchBtn");if(!e||!t)return;const i=()=>{o.query=e.value.trim(),o.query.length>=2?W():l()};t.addEventListener("click",i),e.addEventListener("keydown",n=>{n.key==="Enter"&&i()}),e.addEventListener("input",()=>{clearTimeout(o.debounceTimer),o.debounceTimer=setTimeout(i,450)})}function W(){const e=document.getElementById("destinationsGrid"),t=document.getElementById("resultsInfo");e&&(t&&(t.textContent="Searching..."),e.innerHTML=T(),c("/api/discover/search?q="+encodeURIComponent(o.query)).then(i=>{const n=i||[];if(!n.length){e.innerHTML='<div class="empty-state"><i class="fas fa-search"></i><p>No destinations found for "'+o.query+'". Try a different search.</p></div>',t&&(t.textContent="");return}t&&(t.textContent=n.length+" result"+(n.length!==1?"s":"")+" found");const s=n.map(a=>({id:a.id,name:a.name,country:a.country,description:a.description||"",image_url:a.image_url||"https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&q=80",price_from:0,mood:"general",badge:a.type==="new"?"New":null}));C(s)}).catch(i=>{console.error("Search error:",i),e.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not search destinations. Please try again.</p><p style="font-size:13px;color:var(--text-muted);margin-top:8px;">Error: '+i.message+"</p></div>",t&&(t.textContent="")}))}function y(){M(),j(),q(),R(),l(),g()}document.readyState!=="loading"?y():document.addEventListener("DOMContentLoaded",y);typeof window.Currency<"u"?window.Currency.onCurrencyChange(function(){l(),g()}):document.addEventListener("DOMContentLoaded",function(){typeof window.Currency<"u"&&window.Currency.onCurrencyChange(function(){l(),g()})});let f=null;function E(e,t){const i=document.getElementById("destinationInsights"),n=document.getElementById("insightsDestination");!i||!n||(f=t||e,n.textContent=e+(t?", "+t:""),i.style.display="block",i.scrollIntoView({behavior:"smooth",block:"nearest"}),A(f),O(f),G())}function N(){const e=document.getElementById("destinationInsights");e&&(e.style.display="none"),f=null}window.closeInsights=N;async function A(e){const t=document.getElementById("newsContent");if(t){t.innerHTML='<div class="insight-loading"><i class="fas fa-spinner fa-spin"></i> Loading news...</div>';try{const n=await(await fetch(`/api/destination-news?destination=${encodeURIComponent(e)}`)).json();n.success&&n.articles&&n.articles.length>0?t.innerHTML=n.articles.slice(0,5).map(s=>`
                <div class="insight-item">
                    <div class="insight-item-title">
                        <i class="fas fa-newspaper"></i>
                        ${d(s.title)}
                    </div>
                    ${s.description?`<div class="insight-item-desc">${d(s.description)}</div>`:""}
                    <div class="insight-item-meta">
                        ${s.source?`<span><i class="fas fa-building"></i> ${d(s.source)}</span>`:""}
                        ${s.publishedAt?`<span><i class="fas fa-clock"></i> ${P(s.publishedAt)}</span>`:""}
                    </div>
                    ${s.url?`<a href="${s.url}" target="_blank" rel="noopener" class="insight-item-link">Read more <i class="fas fa-external-link-alt"></i></a>`:""}
                </div>
            `).join(""):t.innerHTML='<div class="insight-empty"><i class="fas fa-newspaper"></i>No recent news available for this destination.</div>'}catch(i){console.error("Failed to load news:",i),t.innerHTML='<div class="insight-empty"><i class="fas fa-exclamation-circle"></i>Failed to load news. Please try again later.</div>'}}}async function O(e){const t=document.getElementById("sitesContent");if(t){t.innerHTML='<div class="insight-loading"><i class="fas fa-spinner fa-spin"></i> Loading sites...</div>';try{const n=await(await fetch(`https://en.wikipedia.org/api/rest_v1/page/related/${encodeURIComponent(e)}`)).json();if(n.pages&&n.pages.length>0){const s=n.pages.filter(a=>a.description&&(a.description.toLowerCase().includes("museum")||a.description.toLowerCase().includes("monument")||a.description.toLowerCase().includes("palace")||a.description.toLowerCase().includes("temple")||a.description.toLowerCase().includes("church")||a.description.toLowerCase().includes("castle")||a.description.toLowerCase().includes("park")||a.description.toLowerCase().includes("landmark"))).slice(0,6);s.length>0?t.innerHTML=s.map(a=>`
                    <div class="insight-item">
                        <div class="insight-item-title">
                            <i class="fas fa-map-marker-alt"></i>
                            ${d(a.title)}
                        </div>
                        ${a.description?`<div class="insight-item-desc">${d(a.description)}</div>`:""}
                        ${a.content_urls&&a.content_urls.desktop?`<a href="${a.content_urls.desktop.page}" target="_blank" rel="noopener" class="insight-item-link">Learn more <i class="fas fa-external-link-alt"></i></a>`:""}
                    </div>
                `).join(""):p(e,t)}else p(e,t)}catch(i){console.error("Failed to load tourist sites:",i),p(e,t)}}}function p(e,t){const i=[{icon:"fa-landmark",title:"Historical Landmarks",desc:"Explore ancient monuments and historical sites"},{icon:"fa-building",title:"Museums & Galleries",desc:"Discover art, culture, and history"},{icon:"fa-tree",title:"Parks & Gardens",desc:"Enjoy nature and outdoor spaces"},{icon:"fa-utensils",title:"Local Markets",desc:"Experience authentic local culture"},{icon:"fa-camera",title:"Photo Spots",desc:"Capture memorable moments"}];t.innerHTML=i.map(n=>`
        <div class="insight-item">
            <div class="insight-item-title">
                <i class="fas ${n.icon}"></i>
                ${n.title}
            </div>
            <div class="insight-item-desc">${n.desc}</div>
        </div>
    `).join("")}async function G(e){const t=document.getElementById("thingsContent");if(!t)return;t.innerHTML='<div class="insight-loading"><i class="fas fa-spinner fa-spin"></i> Loading activities...</div>';const n=[{icon:"fa-walking",title:"Walking Tours",desc:"Explore the city on foot with guided tours",popular:!0},{icon:"fa-utensils",title:"Food & Dining",desc:"Try local cuisine and restaurants",popular:!0},{icon:"fa-shopping-bag",title:"Shopping",desc:"Browse local markets and boutiques",popular:!1},{icon:"fa-camera",title:"Photography",desc:"Capture stunning views and landmarks",popular:!0},{icon:"fa-bus",title:"City Tours",desc:"Hop-on hop-off bus tours",popular:!1},{icon:"fa-water",title:"Water Activities",desc:"Beaches, boats, and water sports",popular:!1},{icon:"fa-mountain",title:"Outdoor Adventures",desc:"Hiking, climbing, and nature",popular:!1},{icon:"fa-music",title:"Nightlife & Entertainment",desc:"Bars, clubs, and live music",popular:!0},{icon:"fa-spa",title:"Wellness & Spa",desc:"Relax and rejuvenate",popular:!1},{icon:"fa-ticket-alt",title:"Events & Shows",desc:"Concerts, theater, and performances",popular:!1}].sort(()=>.5-Math.random()).slice(0,6);t.innerHTML=n.map(s=>`
        <div class="insight-item">
            <div class="insight-item-title">
                <i class="fas ${s.icon}"></i>
                ${s.title}
                ${s.popular?'<span style="background:var(--gold);color:var(--deep);font-size:10px;padding:2px 6px;border-radius:3px;margin-left:8px;font-weight:600;">POPULAR</span>':""}
            </div>
            <div class="insight-item-desc">${s.desc}</div>
        </div>
    `).join("")}function P(e){try{const t=new Date(e),n=Math.abs(new Date-t),s=Math.ceil(n/(1e3*60*60*24));return s===0?"Today":s===1?"Yesterday":s<7?`${s} days ago`:s<30?`${Math.floor(s/7)} weeks ago`:t.toLocaleDateString("en-US",{month:"short",day:"numeric",year:"numeric"})}catch{return e}}function d(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}document.addEventListener("click",function(e){const t=e.target.closest(".destination-card");if(t&&!e.target.closest(".wishlist-toggle")&&!e.target.closest(".primary-button")){const i=t.querySelector("h3");if(i){const s=i.textContent.split(","),a=s[0].trim(),m=s[1]?s[1].trim():"";E(a,m)}}});const w=document.getElementById("searchBtn");w&&w.addEventListener("click",function(){const e=document.getElementById("searchInput");e&&e.value.trim()&&setTimeout(()=>{E(e.value.trim(),"")},1e3)});
