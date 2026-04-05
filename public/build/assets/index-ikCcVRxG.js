const k=window.__dashboardConfig||{};var h;const v=((h=document.querySelector('meta[name="csrf-token"]'))==null?void 0:h.content)??"",p=!!k.userId,E={relaxed:"fa-spa",adventurous:"fa-mountain",cultural:"fa-landmark",romantic:"fa-heart",foodie:"fa-utensils",eco:"fa-leaf",eco_tourism:"fa-leaf",beach:"fa-umbrella-beach",mountain:"fa-mountain",historical:"fa-landmark",food_culture:"fa-utensils",nature:"fa-tree",general:"fa-globe"},s={category:"all",region:"all",query:"",debounceTimer:null};let o=new Set;function c(e,n){n=n||"fa-info-circle";const i=document.getElementById("toast");if(!i)return;const t=i.querySelector("i");t&&(t.className="fas "+n);const a=document.getElementById("toastMsg");a&&(a.textContent=e),i.classList.add("show"),setTimeout(()=>i.classList.remove("show"),3e3)}function r(e,n){n=n||{};const i=Object.assign({Accept:"application/json","X-CSRF-TOKEN":v},n.headers||{});return fetch(e,Object.assign({},n,{headers:i,credentials:"same-origin"})).then(t=>{if(!t.ok)throw new Error("HTTP "+t.status);return t.json()})}function S(){const e=new URLSearchParams;return s.category!=="all"&&e.set("category",s.category),s.region!=="all"&&e.set("region",s.region),s.query.trim()&&e.set("q",s.query.trim()),e.toString()?"?"+e.toString():""}function L(){p&&r("/api/wishlist/count").then(e=>{o=new Set(e.ids??[]);const n=document.getElementById("wishlistCount");n&&(n.textContent=e.count??o.size)}).catch(()=>{})}function _(e,n){if(!p){window.location.href="/login";return}const i=o.has(e),t=n.querySelector("i");i?r("/wishlist/"+e,{method:"DELETE"}).then(()=>{o.delete(e),t&&(t.className="far fa-heart"),n.title="Save to Wishlist",c("Removed from wishlist","fa-heart-broken");try{localStorage.setItem("smartBookingWishlistUpdated",String(Date.now()))}catch{}window.__refreshWishlistBadge&&window.__refreshWishlistBadge()}).catch(()=>c("Could not remove","fa-exclamation-circle")):r("/wishlist",{method:"POST",headers:{Accept:"application/json","Content-Type":"application/json","X-CSRF-TOKEN":v},body:JSON.stringify({destination_id:e})}).then(()=>{o.add(e),t&&(t.className="fas fa-heart"),n.title="Remove from Wishlist",c("Saved to Wishlist!","fa-heart");try{localStorage.setItem("smartBookingWishlistUpdated",String(Date.now()))}catch{}window.__refreshWishlistBadge&&window.__refreshWishlistBadge()}).catch(()=>c("Could not save","fa-exclamation-circle"))}function T(e){const n=document.getElementById("destinationsGrid"),i=document.getElementById("resultsInfo");if(n){if(!e.length){n.innerHTML='<div class="empty-state"><i class="fas fa-search"></i><p>No destinations found. Try adjusting your filters.</p></div>',i&&(i.textContent="");return}i&&(i.textContent=e.length+" destination"+(e.length!==1?"s":"")+" found"),n.innerHTML=e.map(t=>{var m;const a=E[(m=t.mood)==null?void 0:m.toLowerCase()]||"fa-globe",d=t.mood?t.mood.replace(/_/g," ").replace(/\b\w/g,w=>w.toUpperCase()):"",y=t.badge?`<span class="dest-badge">${t.badge}</span>`:"",b=t.image_url?`background-image:url('${t.image_url}')`:"",u=o.has(t.id),f=t.price_from?"$"+Number(t.price_from).toLocaleString()+"+":"";return`<div class="destination-card" data-id="${t.id}">
            <div class="destination-image" style="${b}">
                ${y}
                <button class="wishlist-toggle" data-id="${t.id}" title="${u?"Remove from Wishlist":"Save to Wishlist"}">
                    <i class="${u?"fas":"far"} fa-heart"></i>
                </button>
            </div>
            <div class="destination-content">
                <h3>${t.name}${t.country?", "+t.country:""}</h3>
                <div class="destination-meta">
                    ${f?`<span class="price-tag">${f}</span>`:""}
                    ${d?`<span class="mood-indicator"><i class="fas ${a}"></i> ${d}</span>`:""}
                </div>
                <p>${t.description?t.description.substring(0,110)+(t.description.length>110?"…":""):""}</p>
                <a href="/destinations/${t.id}" class="primary-button" style="text-decoration:none;width:100%;justify-content:center;">
                    Explore <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>`}).join(""),n.querySelectorAll(".wishlist-toggle").forEach(t=>{t.addEventListener("click",a=>{a.preventDefault(),a.stopPropagation(),_(Number(t.dataset.id),t)})})}}function $(e){return e=e||6,Array.from({length:e},()=>`<div class="destination-card">
            <div class="destination-image skeleton"></div>
            <div class="destination-content">
                <div class="sk-line medium skeleton"></div>
                <div class="sk-line short skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line full skeleton"></div>
                <div class="sk-line medium skeleton" style="margin-top:10px;height:36px;border-radius:4px;"></div>
            </div>
        </div>`).join("")}function l(){const e=document.getElementById("destinationsGrid"),n=document.getElementById("resultsInfo");e&&(n&&(n.textContent=""),e.innerHTML=$(),r("/api/discover/destinations"+S()).then(i=>T(i.data??i)).catch(()=>{e.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Could not load destinations. Please try again.</p></div>'}))}function B(e){const n=document.getElementById("hiddenGemsGrid");if(n){if(!e.length){n.innerHTML='<p style="color:rgba(255,255,255,.6);grid-column:1/-1;text-align:center;">No hidden gems found.</p>';return}n.innerHTML=e.map(i=>{const t=i.image_url?`background-image:url('${i.image_url}')`:"";return`<a href="/destinations/${i.id}" class="featured-card" style="text-decoration:none;display:block;">
            <div class="feat-img" style="${t}"></div>
            <div class="feat-body">
                <h4>${i.name}${i.country?", "+i.country:""}</h4>
                <p>${i.description?i.description.substring(0,80)+(i.description.length>80?"…":""):""}</p>
                ${i.match_score?`<span class="feat-tag">${i.match_score}% Match</span>`:""}
            </div>
        </a>`}).join("")}}function C(){const e=document.getElementById("hiddenGemsGrid");e&&(e.innerHTML=Array.from({length:3},()=>`<div class="featured-card">
            <div class="feat-img skeleton"></div>
            <div class="feat-body">
                <div class="sk-line medium skeleton" style="background:rgba(255,255,255,.15);"></div>
                <div class="sk-line full skeleton" style="background:rgba(255,255,255,.1);margin-top:6px;"></div>
            </div>
        </div>`).join(""),r("/api/discover/hidden-gems").then(n=>B(n.data??n)).catch(()=>{e&&(e.innerHTML="")}))}function I(){const e=document.getElementById("filterTabs");e&&e.addEventListener("click",n=>{const i=n.target.closest(".filter-tab");i&&(document.querySelectorAll(".filter-tab").forEach(t=>t.classList.remove("active")),i.classList.add("active"),s.category=i.dataset.category,l())})}function x(){const e=document.getElementById("regionRow");e&&e.addEventListener("click",n=>{const i=n.target.closest(".region-pill");i&&(document.querySelectorAll(".region-pill").forEach(t=>t.classList.remove("active")),i.classList.add("active"),s.region=i.dataset.region,l())})}function H(){const e=document.getElementById("searchInput"),n=document.getElementById("searchBtn");if(!e||!n)return;const i=()=>{s.query=e.value,l()};n.addEventListener("click",i),e.addEventListener("keydown",t=>{t.key==="Enter"&&i()}),e.addEventListener("input",()=>{clearTimeout(s.debounceTimer),s.debounceTimer=setTimeout(i,450)})}function g(){L(),I(),x(),H(),l(),C()}document.readyState!=="loading"?g():document.addEventListener("DOMContentLoaded",g);
