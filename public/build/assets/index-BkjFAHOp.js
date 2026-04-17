const E=window.__dashboardConfig||{};var y;const p=((y=document.querySelector('meta[name="csrf-token"]'))==null?void 0:y.content)??"",v=!!E.userId,L={relaxed:"fa-spa",adventurous:"fa-mountain",cultural:"fa-landmark",romantic:"fa-heart",foodie:"fa-utensils",eco:"fa-leaf",eco_tourism:"fa-leaf",beach:"fa-umbrella-beach",mountain:"fa-mountain",historical:"fa-landmark",food_culture:"fa-utensils",nature:"fa-tree",general:"fa-globe"},s={category:"all",region:"all",query:"",debounceTimer:null};let o=new Set;function d(e,i){i=i||"fa-info-circle";const n=document.getElementById("toast");if(!n)return;const t=n.querySelector("i");t&&(t.className="fas "+i);const a=document.getElementById("toastMsg");a&&(a.textContent=e),n.classList.add("show"),setTimeout(()=>n.classList.remove("show"),3e3)}function c(e,i){i=i||{};const n=Object.assign({Accept:"application/json","X-CSRF-TOKEN":p},i.headers||{});return fetch(e,Object.assign({},i,{headers:n,credentials:"same-origin"})).then(t=>{if(!t.ok)throw new Error("HTTP "+t.status);return t.json()})}function S(){const e=new URLSearchParams;return s.category!=="all"&&e.set("category",s.category),s.region!=="all"&&e.set("region",s.region),s.query.trim()&&e.set("q",s.query.trim()),e.toString()?"?"+e.toString():""}function C(){v&&c("/api/wishlist/count").then(e=>{o=new Set(e.ids??[]);const i=document.getElementById("wishlistCount");i&&(i.textContent=e.count??o.size)}).catch(()=>{})}function _(e,i){if(!v){window.location.href="/login";return}const n=o.has(e),t=i.querySelector("i");n?c("/wishlist/"+e,{method:"DELETE"}).then(()=>{o.delete(e),t&&(t.className="far fa-heart"),i.title="Save to Wishlist",d("Removed from wishlist","fa-heart-broken");try{localStorage.setItem("smartBookingWishlistUpdated",String(Date.now()))}catch{}window.__refreshWishlistBadge&&window.__refreshWishlistBadge()}).catch(()=>d("Could not remove","fa-exclamation-circle")):c("/wishlist",{method:"POST",headers:{Accept:"application/json","Content-Type":"application/json","X-CSRF-TOKEN":p},body:JSON.stringify({destination_id:e})}).then(()=>{o.add(e),t&&(t.className="fas fa-heart"),i.title="Remove from Wishlist",d("Saved to Wishlist!","fa-heart");try{localStorage.setItem("smartBookingWishlistUpdated",String(Date.now()))}catch{}window.__refreshWishlistBadge&&window.__refreshWishlistBadge()}).catch(()=>d("Could not save","fa-exclamation-circle"))}function T(e){const i=document.getElementById("destinationsGrid"),n=document.getElementById("resultsInfo");if(i){if(!e.length){i.innerHTML='<div class="empty-state"><i class="fas fa-search"></i><p>No destinations found. Try adjusting your filters.</p></div>',n&&(n.textContent="");return}n&&(n.textContent=e.length+" destination"+(e.length!==1?"s":"")+" found"),i.innerHTML=e.map(t=>{var g;const a=L[(g=t.mood)==null?void 0:g.toLowerCase()]||"fa-globe",u=t.mood?t.mood.replace(/_/g," ").replace(/\b\w/g,k=>k.toUpperCase()):"",w=t.badge?`<span class="dest-badge">${t.badge}</span>`:"",b=t.image_url?`background-image:url('${t.image_url}')`:"",f=o.has(t.id),m=t.price_from>0?"<span>"+(t.currency&&typeof window.Currency<"u"?window.Currency.symbol(t.currency):"$")+t.price_from.toLocaleString()+"+</span>":"";return`<div class="destination-card" data-id="${t.id}">
            <div class="destination-image" style="${b}">
                ${w}
                <button class="wishlist-toggle" data-id="${t.id}" title="${f?"Remove from Wishlist":"Save to Wishlist"}">
                    <i class="${f?"fas":"far"} fa-heart"></i>
                </button>
            </div>
            <div class="destination-content">
                <h3>${t.name}${t.country?", "+t.country:""}</h3>
                <div class="destination-meta">
                    ${m?`<span class="price-tag">${m}</span>`:""}
                    ${u?`<span class="mood-indicator"><i class="fas ${a}"></i> ${u}</span>`:""}
                </div>
                <p>${t.description?t.description.substring(0,110)+(t.description.length>110?"…":""):""}</p>
                <a href="/destinations/${t.id}" class="primary-button" style="text-decoration:none;width:100%;justify-content:center;">
                    Explore <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>`}).join(""),i.querySelectorAll(".wishlist-toggle").forEach(t=>{t.addEventListener("click",a=>{a.preventDefault(),a.stopPropagation(),_(Number(t.dataset.id),t)})})}}function $(e){return e=e||6,Array.from({length:e},()=>`<div class="destination-card">
            <div class="destination-image skeleton"></div>
            <div class="destination-content">
                <div class="sk-line medium skeleton"></div>
                <div class="sk-line short skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton" style="margin-top:10px;height:36px;border-radius:4px;"></div>
            </div>
        </div>`).join("")}function r(){const e=document.getElementById("destinationsGrid"),i=document.getElementById("resultsInfo");e&&(i&&(i.textContent=""),e.innerHTML=$(),c("/api/discover/destinations"+S()).then(n=>T(n.data??n)).catch(()=>{e.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not load destinations. Please try again.</p></div>'}))}function B(e){const i=document.getElementById("hiddenGemsGrid");if(i){if(!e.length){i.innerHTML='<p style="color:rgba(255,255,255,.6);grid-column:1/-1;text-align:center;">No hidden gems found.</p>';return}i.innerHTML=e.map(n=>{const t=n.image_url?`background-image:url('${n.image_url}')`:"";return`<a href="/destinations/${n.id}" class="featured-card" style="text-decoration:none;display:block;">
            <div class="feat-img" style="${t}"></div>
            <div class="feat-body">
                <h4>${n.name}${n.country?", "+n.country:""}</h4>
                <p>${n.description?n.description.substring(0,80)+(n.description.length>80?"…":""):""}</p>
                ${n.match_score?`<span class="feat-tag">${n.match_score}% Match</span>`:""}
            </div>
        </a>`}).join("")}}function l(){const e=document.getElementById("hiddenGemsGrid");e&&(e.innerHTML=Array.from({length:3},()=>`<div class="featured-card">
            <div class="feat-img skeleton"></div>
            <div class="feat-body">
                <div class="sk-line medium skeleton" style="background:rgba(255,255,255,.15);"></div>
                <div class="sk-line full skeleton" style="background:rgba(255,255,255,.1);margin-top:6px;"></div>
            </div>
        </div>`).join(""),c("/api/discover/hidden-gems").then(i=>B(i.data??i)).catch(()=>{e&&(e.innerHTML="")}))}function I(){const e=document.getElementById("filterTabs");e&&e.addEventListener("click",i=>{const n=i.target.closest(".filter-tab");n&&(document.querySelectorAll(".filter-tab").forEach(t=>t.classList.remove("active")),n.classList.add("active"),s.category=n.dataset.category,r())})}function x(){const e=document.getElementById("regionRow");e&&e.addEventListener("click",i=>{const n=i.target.closest(".region-pill");n&&(document.querySelectorAll(".region-pill").forEach(t=>t.classList.remove("active")),n.classList.add("active"),s.region=n.dataset.region,r())})}function H(){const e=document.getElementById("searchInput"),i=document.getElementById("searchBtn");if(!e||!i)return;const n=()=>{s.query=e.value,r()};i.addEventListener("click",n),e.addEventListener("keydown",t=>{t.key==="Enter"&&n()}),e.addEventListener("input",()=>{clearTimeout(s.debounceTimer),s.debounceTimer=setTimeout(n,450)})}function h(){C(),I(),x(),H(),r(),l()}document.readyState!=="loading"?h():document.addEventListener("DOMContentLoaded",h);typeof window.Currency<"u"?window.Currency.onCurrencyChange(function(){r(),l()}):document.addEventListener("DOMContentLoaded",function(){typeof window.Currency<"u"&&window.Currency.onCurrencyChange(function(){r(),l()})});
