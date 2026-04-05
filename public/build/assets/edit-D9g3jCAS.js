const a=window.__dashboardConfig&&window.__dashboardConfig.user?window.__dashboardConfig.user:{name:"",firstName:"",avatar:"",type:"",verified:!1,id:null};function ne(){const e=document.getElementById("welcomeMessage");if(e.textContent=`Welcome Back, ${a.firstName}!`,a.avatar&&a.avatar!=="")document.querySelectorAll(".user-avatar img, .nav-profile-pic img").forEach(i=>{i&&(i.src=a.avatar,i.style.display="block")}),document.querySelectorAll(".avatar-placeholder, .placeholder").forEach(i=>{i.style.display="none"});else{const t=a.name.split(" ").map(o=>o[0]).join("").toUpperCase().substring(0,2);document.querySelectorAll(".avatar-placeholder, .placeholder").forEach(o=>{o.textContent=t,o.style.display="flex"}),document.querySelectorAll(".user-avatar img, .nav-profile-pic img").forEach(o=>{o&&(o.style.display="none")})}document.getElementById("userName").textContent=a.name;const n=document.getElementById("userTypeBadge");if(n){n.className=`user-type-badge ${a.type}`;const t=document.getElementById("userTypeText");t&&(t.textContent=a.type.charAt(0).toUpperCase()+a.type.slice(1))}oe()}function oe(){fetch("/api/user/statistics").then(e=>e.json()).then(e=>{Z(e)}).catch(e=>{console.log("Using default counts")})}function Z(e=null){const n=(e==null?void 0:e.photos)||r.length,t=(e==null?void 0:e.trips)||0,i=(e==null?void 0:e.bookings)||0,o=(e==null?void 0:e.saved)||0,m=(e==null?void 0:e.notifications)||0,T=document.getElementById("photosCount"),k=document.getElementById("statPhotosCount"),A=document.getElementById("bookingsCount"),I=document.getElementById("statBookingsCount"),M=document.getElementById("savedCount"),L=document.getElementById("statSavedCount"),U=document.getElementById("statTripsCount"),y=document.getElementById("notificationCount");T&&(T.textContent=n),k&&(k.textContent=n),A&&(A.textContent=i),I&&(I.textContent=i),M&&(M.textContent=o),L&&(L.textContent=o),U&&(U.textContent=t),y&&(y.textContent=m,y.style.display=m>0?"block":"none")}let r=[],d=new Set,S=0,s=[],x="all",l=0,v=null;document.addEventListener("DOMContentLoaded",function(){ne(),me(),p(),ie(),setInterval(p,5e3)});function ie(){const e=window.__dashboardConfig&&window.__dashboardConfig.pusherKey||"",n=window.__dashboardConfig&&window.__dashboardConfig.pusherCluster||"mt1",t=window.__dashboardConfig&&window.__dashboardConfig.userId||null;if(!e||!t){w();return}if(typeof Pusher<"u")try{v=new Pusher(e,{cluster:n,encrypted:!0}).subscribe("private-user."+t),v.bind("new-chat-message",function(o){ae(o)}),v.bind("notification",function(o){se(o)}),console.log("Real-time chat initialized with Pusher")}catch{console.log("Pusher not available, using polling fallback"),w()}else console.log("Pusher library not loaded, using polling fallback"),w()}function w(){setInterval(()=>{p(!0)},2e3)}function ae(e){const n={id:e.message_id||Date.now(),type:"chat",title:`New chat from ${e.sender_name}`,message:e.content,time:"Just now",read:!1,user:{name:e.sender_name,avatar:e.sender_avatar,initials:e.sender_initials}};s.unshift(n),l++,u(),c(),le(),re(e.sender_name,e.content)}function se(e){s.unshift(e),e.read||l++,u(),c()}function re(e,n){const t=document.createElement("div");t.style.cssText=`
                position: fixed;
                top: 80px;
                right: 20px;
                background: white;
                border: 2px solid var(--gold);
                border-radius: 12px;
                padding: 15px 20px;
                box-shadow: 0 8px 24px rgba(59, 31, 43, 0.2);
                z-index: 10000;
                min-width: 300px;
                max-width: 400px;
                animation: slideInRight 0.4s ease;
                cursor: pointer;
            `;const i=n.length>60?n.substring(0,60)+"...":n;t.innerHTML=`
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--gold), var(--deep)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px;">
                        ${e.split(" ").map(o=>o[0]).join("").toUpperCase().substring(0,2)}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: var(--deep); margin-bottom: 3px;">
                            <i class="fas fa-comments" style="color: var(--gold);"></i>
                            ${e}
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted);">
                            ${i}
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 16px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `,t.onclick=function(){window.location.href="/chat",this.remove()},document.body.appendChild(t),setTimeout(()=>{t.style.animation="slideOutRight 0.4s ease",setTimeout(()=>t.remove(),400)},5e3)}function le(){try{const e=new Audio("data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBDGH0fPTgjMGHm7A7+OZUA0PVqzn77BdGgc+ltryxnYpBSh+zPLaizsIGGS57OihUxELTKXh8bllHAU2kNXzzn0vBSh6yfDajDwIFmq+7eibUg4OVKzl8LRfGgc8ldjywngqBCh9y/HajjwIFmm97OmgURALTqPi8bllHAU3kdXzzoAuBSh6yfDajjsJFWq97OmgUg0PVanl8LVfGgc8ldryw3kpBCd9y/DajjsJFWq+7OmfUhAMTqPh8bhnHgU3kdXzzn4vBCh6yfDajjsJFWq+7OidUREMTqPh8bhmHQU3kdXzzn4vBCd7yfDajjsJFmq97OmdUREMTqTg8bhmHQU3kdTzz34uBSd7yfDajjsJFmq97OmdUREMT6Th8bhpHgU2kNTzzoAuBSd7yfDbjTsIFmq97OicUhAMT6Tg8bppHgU2kNTzz4AuBSZ7yfDbkToJFWq97Omc");e.volume=.3,e.play().catch(()=>{})}catch{console.log("Could not play notification sound")}}const ee=document.createElement("style");ee.textContent=`
            @keyframes slideInRight {
                from { transform: translateX(400px); opacity: 0; }
                to   { transform: translateX(0);     opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0);     opacity: 1; }
                to   { transform: translateX(400px); opacity: 0; }
            }
        `;document.head.appendChild(ee);function p(e=!1){fetch("/api/notifications").then(n=>n.json()).then(n=>{s=n.notifications||N(),l=s.filter(t=>!t.read).length,u(),c()}).catch(n=>{e||console.log("Using sample notifications"),s=N(),l=s.filter(t=>!t.read).length,u(),c()})}function N(){return[{id:1,type:"chat",title:"New chat from Sarah Johnson",message:"Hey! I saw you're planning a trip to Bali. I have some great recommendations!",time:"5 minutes ago",read:!1,user:{name:"Sarah Johnson",avatar:null,initials:"SJ"}},{id:2,type:"booking",title:"Booking Confirmed",message:"Your flight to Tokyo has been confirmed. Check-in opens 24 hours before departure.",time:"2 hours ago",read:!1},{id:3,type:"chat",title:"Michael Roberts sent you a chat",message:"Thanks for the travel tips! The restaurant you recommended was amazing.",time:"5 hours ago",read:!0,user:{name:"Michael Roberts",avatar:null,initials:"MR"}},{id:4,type:"trip",title:"Trip Reminder",message:"Your trip to Paris starts in 5 days. Don't forget to pack!",time:"1 day ago",read:!1},{id:5,type:"photo",title:"Photos Uploaded",message:"Successfully uploaded 24 photos to your Bali album.",time:"2 days ago",read:!0},{id:6,type:"chat",title:"Anna Chen mentioned you",message:'Anna Chen mentioned you in a chat: "You should check out this place!"',time:"2 days ago",read:!0,user:{name:"Anna Chen",avatar:null,initials:"AC"}},{id:7,type:"booking",title:"Price Drop Alert",message:"Good news! The hotel you saved in Santorini dropped by 25%.",time:"3 days ago",read:!0},{id:8,type:"system",title:"Account Verified",message:"Congratulations! Your account has been successfully verified.",time:"1 week ago",read:!0}]}function P(){const e=document.getElementById("notificationDropdown");e.classList.toggle("active"),e.classList.contains("active")&&setTimeout(()=>{de()},1e3)}function z(e){x=e,document.querySelectorAll(".notification-tab").forEach(n=>{n.classList.remove("active")}),document.querySelector(`[data-tab="${e}"]`).classList.add("active"),c()}function c(){const e=document.getElementById("notificationList");let n=s;if(x==="chat"?n=s.filter(t=>t.type==="chat"):x==="activity"&&(n=s.filter(t=>t.type!=="chat")),n.length===0){e.innerHTML=`
                    <div class="empty-notifications">
                        <i class="fas fa-bell-slash"></i>
                        <h4>No notifications</h4>
                        <p>You're all caught up!</p>
                    </div>
                `;return}e.innerHTML=n.map(t=>{const i=ce(t.type),o=t.user?t.user.avatar?`<img src="${t.user.avatar}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">`:`<div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">${t.user.initials}</div>`:`<i class="${i}"></i>`;return`
                    <div class="notification-item ${t.read?"":"unread"}" onclick="handleNotificationClick(${t.id})">
                        <div class="notification-icon-wrapper ${t.type}">
                            ${o}
                        </div>
                        <div class="notification-content">
                            <h4>${t.title}</h4>
                            <p>${t.message}</p>
                            <div class="notification-time">
                                <i class="fas fa-clock"></i>
                                ${t.time}
                            </div>
                        </div>
                    </div>
                `}).join("")}function ce(e){return{chat:"fas fa-comments",booking:"fas fa-ticket-alt",trip:"fas fa-route",photo:"fas fa-images",system:"fas fa-info-circle"}[e]||"fas fa-bell"}function u(){const e=document.getElementById("notificationCount");l>0?(e.textContent=l>99?"99+":l,e.style.display="block"):e.style.display="none"}function j(){s=s.map(e=>({...e,read:!0})),l=0,u(),c(),fetch("/api/notifications/mark-all-read",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content}}).catch(console.error),Swal.fire({title:"All notifications marked as read",icon:"success",timer:1500,showConfirmButton:!1})}function de(){const e=s.filter(t=>!t.read);if(e.length===0)return;const n=e.map(t=>t.id);s=s.map(t=>n.includes(t.id)?{...t,read:!0}:t),l=s.filter(t=>!t.read).length,u(),c(),fetch("/api/notifications/mark-read",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({ids:n})}).catch(console.error)}document.addEventListener("click",function(e){const n=document.getElementById("notificationDropdown"),t=document.querySelector(".notification-btn");!n.contains(e.target)&&!t.contains(e.target)&&n.classList.remove("active")});function D(){Swal.fire({title:'<i class="fas fa-comments"></i> Send API Chat Message',html:`
                    <div style="text-align: left; padding: 10px 20px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--deep);">
                                <i class="fas fa-user"></i> To:
                            </label>
                            <input type="text" id="userSearch" placeholder="Search users..."
                                style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px;"
                                oninput="searchUsers(this.value)">
                            <div id="userSearchResults" style="max-height: 150px; overflow-y: auto; margin-top: 10px; border: 1px solid var(--border); border-radius: 8px; display: none;">
                            </div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <div id="selectedUser" style="display: none; padding: 12px; background: rgba(201, 169, 110, 0.1); border-radius: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div id="selectedUserAvatar"></div>
                                    <div>
                                        <div id="selectedUserName" style="font-weight: 600; color: var(--deep);"></div>
                                        <div id="selectedUserType" style="font-size: 12px; color: var(--text-muted);"></div>
                                    </div>
                                    <button onclick="clearSelectedUser()" style="margin-left: auto; background: none; border: none; color: var(--danger); cursor: pointer; font-size: 18px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--deep);">
                                <i class="fas fa-comments"></i> Chat Message:
                            </label>
                            <textarea id="messageContent" placeholder="Type your chat message here..."
                                style="width: 100%; min-height: 120px; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Georgia', serif; resize: vertical;"
                                maxlength="1000"></textarea>
                            <div style="text-align: right; font-size: 12px; color: var(--text-muted); margin-top: 5px;">
                                <span id="charCount">0</span>/1000 characters
                            </div>
                        </div>
                        <input type="hidden" id="selectedUserId" value="">
                    </div>
                `,width:600,showCancelButton:!0,confirmButtonColor:"#c9a96e",cancelButtonColor:"#f44336",confirmButtonText:'<i class="fas fa-paper-plane"></i> Send Chat',cancelButtonText:"Cancel",showLoaderOnConfirm:!0,didOpen:()=>{document.getElementById("messageContent").addEventListener("input",function(){document.getElementById("charCount").textContent=this.value.length})},preConfirm:()=>{const e=document.getElementById("selectedUserId").value,n=document.getElementById("messageContent").value.trim();return e?n?n.length>1e3?(Swal.showValidationMessage("Message is too long (max 1000 characters)"),!1):ue(e,n):(Swal.showValidationMessage("Please enter a chat message"),!1):(Swal.showValidationMessage("Please select a user to chat with"),!1)}}).then(e=>{e.isConfirmed&&e.value&&(Swal.fire({title:"Chat Message Sent!",text:"Your message has been delivered in real-time.",icon:"success",confirmButtonColor:"#c9a96e",timer:2e3}),p())})}async function ue(e,n){try{const t=await fetch("/api/chat/send",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content,Accept:"application/json"},body:JSON.stringify({receiver_id:e,content:n})});if(!t.ok)throw new Error("Failed to send chat message");const i=await t.json(),o=document.createElement("div");return o.style.cssText=`
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
                    color: white;
                    padding: 15px 25px;
                    border-radius: 12px;
                    box-shadow: 0 8px 24px rgba(201, 169, 110, 0.4);
                    z-index: 10000;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    animation: slideInUp 0.4s ease;
                `,o.innerHTML=`
                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    Chat message sent in real-time!
                `,document.body.appendChild(o),setTimeout(()=>{o.style.animation="slideOutDown 0.4s ease",setTimeout(()=>o.remove(),400)},3e3),i}catch(t){return console.error("Error sending chat message:",t),Swal.showValidationMessage("Failed to send chat. Please try again."),!1}}const te=document.createElement("style");te.textContent=`

                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(100px);
                    opacity: 0;
                }
            }
        `;document.head.appendChild(te);function $(){document.getElementById("sidebar").classList.toggle("active")}function b(){document.getElementById("galleryModal").classList.add("active"),g()}function O(){document.getElementById("galleryModal").classList.remove("active")}function q(){document.getElementById("mediaInput").click()}function C(e){Array.from(e.target.files).forEach(t=>{const i=new FileReader;i.onload=function(o){const m={id:Date.now()+Math.random(),type:t.type.startsWith("image/")?"image":"video",src:o.target.result,name:t.name,date:new Date().toISOString()};r.push(m),E(),g(),h()},i.readAsDataURL(t)}),e.target.value=""}const f=document.getElementById("uploadArea");["dragenter","dragover","dragleave","drop"].forEach(e=>{f.addEventListener(e,fe,!1)});function fe(e){e.preventDefault(),e.stopPropagation()}["dragenter","dragover"].forEach(e=>{f.addEventListener(e,()=>{f.classList.add("dragover")})});["dragleave","drop"].forEach(e=>{f.addEventListener(e,()=>{f.classList.remove("dragover")})});f.addEventListener("drop",function(e){const t=e.dataTransfer.files;document.getElementById("mediaInput").files=t,C({target:{files:t}})});function g(){const e=document.getElementById("galleryGrid");if(r.length===0){e.innerHTML="";return}e.innerHTML=r.map((n,t)=>`
                <div class="gallery-item" onclick="viewMedia(${t})">
                    ${n.type==="image"?`<img src="${n.src}" alt="${n.name}">`:`<video src="${n.src}"></video>
                        <div class="video-badge">
                            <i class="fas fa-play"></i>
                            Video
                        </div>`}
                </div>
            `).join("")}function F(e){S=e;const n=r[e],t=document.getElementById("viewerContent");n.type==="image"?t.innerHTML=`<img src="${n.src}" alt="${n.name}">`:t.innerHTML=`<video src="${n.src}" controls autoplay></video>`,document.getElementById("mediaViewer").classList.add("active")}function B(){document.getElementById("mediaViewer").classList.remove("active");const e=document.getElementById("viewerContent");e.innerHTML=""}function R(){Swal.fire({title:"Edit Media",html:`
                    <div style="text-align: left;">
                        <p><strong>Editing Features:</strong></p>
                        <ul style="margin-left: 20px;">
                            <li>Crop & Rotate</li>
                            <li>Filters & Adjustments</li>
                            <li>Add Text & Stickers</li>
                            <li>Drawing Tools</li>
                        </ul>
                    </div>
                `,icon:"info",confirmButtonColor:"#c9a96e",confirmButtonText:"Open Editor"})}function _(){const e=r[S],n=document.createElement("a");n.href=e.src,n.download=e.name,n.click(),Swal.fire({title:"Downloaded!",text:"Media has been saved to your device",icon:"success",confirmButtonColor:"#c9a96e",timer:2e3})}function H(){Swal.fire({title:"Share Media",text:"Choose how you want to share this media",icon:"info",showCancelButton:!0,confirmButtonColor:"#c9a96e",confirmButtonText:"Copy Link"})}function G(){Swal.fire({title:"Delete Media?",text:"This action cannot be undone",icon:"warning",showCancelButton:!0,confirmButtonColor:"#f44336",cancelButtonColor:"#6b5b4f",confirmButtonText:"Yes, delete it"}).then(e=>{e.isConfirmed&&(r.splice(S,1),E(),h(),B(),g(),Swal.fire({title:"Deleted!",text:"Media has been removed",icon:"success",confirmButtonColor:"#c9a96e",timer:2e3}))})}function V(){d=new Set(r.map((e,n)=>n)),Swal.fire({title:"All Selected",text:`${r.length} items selected`,icon:"success",confirmButtonColor:"#c9a96e",timer:1500})}function J(){if(d.size===0){Swal.fire({title:"No Selection",text:"Please select items first",icon:"warning",confirmButtonColor:"#c9a96e"});return}Swal.fire({title:`Delete ${d.size} items?`,text:"This action cannot be undone",icon:"warning",showCancelButton:!0,confirmButtonColor:"#f44336",cancelButtonColor:"#6b5b4f",confirmButtonText:"Yes, delete them"}).then(e=>{e.isConfirmed&&(r=r.filter((n,t)=>!d.has(t)),d.clear(),E(),h(),g(),Swal.fire({title:"Deleted!",text:"Selected items have been removed",icon:"success",confirmButtonColor:"#c9a96e",timer:2e3}))})}function Y(){Swal.fire({title:"Share Selected",text:`Share ${d.size} selected items`,icon:"info",confirmButtonColor:"#c9a96e"})}function E(){localStorage.setItem("smartBookingMedia",JSON.stringify(r))}function me(){const e=localStorage.getItem("smartBookingMedia");e&&(r=JSON.parse(e),h())}function h(){const e=r.length,n={photos:e,trips:0,bookings:0,saved:0,notifications:e>0?1:0};Z(n)}function X(){b()}function Q(){Swal.fire({title:"Your Profile",html:`
                    <div style="text-align: center; margin-bottom: 20px;">
                        ${a.avatar?`<img src="${a.avatar}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--gold);">`:`<div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--deep)); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">${a.name.split(" ").map(e=>e[0]).join("").toUpperCase().substring(0,2)}</div>`}
                    </div>
                    <div style="text-align: left; padding: 0 20px;">
                        <p style="margin: 10px 0;"><strong>Name:</strong> ${a.name}</p>
                        <p style="margin: 10px 0;"><strong>User Type:</strong> ${a.type.charAt(0).toUpperCase()+a.type.slice(1)}</p>
                        <p style="margin: 10px 0;"><strong>Verified:</strong> ${a.verified?"✅ Yes":"❌ No"}</p>
                        <p style="margin: 10px 0;"><strong>User ID:</strong> ${a.id||"N/A"}</p>
                    </div>
                `,confirmButtonColor:"#c9a96e",confirmButtonText:"Edit Profile",showCancelButton:!0,cancelButtonText:"Close"}).then(e=>{e.isConfirmed&&(window.location.href="/profile/edit")})}function K(){Swal.fire({title:"Settings",html:`
                    <div style="text-align: left; padding: 0 20px;">
                        <h4 style="margin-top: 20px;">Account Settings</h4>
                        <p>• Update profile information</p>
                        <p>• Change password</p>
                        <p>• Privacy settings</p>
                        <h4 style="margin-top: 20px;">Notification Preferences</h4>
                        <p>• Email notifications</p>
                        <p>• Push notifications</p>
                        <h4 style="margin-top: 20px;">Travel Preferences</h4>
                        <p>• Default budget range</p>
                        <p>• Preferred destinations</p>
                    </div>
                `,confirmButtonColor:"#c9a96e",confirmButtonText:"Go to Settings",showCancelButton:!0,cancelButtonText:"Close"}).then(e=>{e.isConfirmed&&(window.location.href="/settings")})}function W(){Swal.fire({title:"Logout",text:"Are you sure you want to logout?",icon:"warning",showCancelButton:!0,confirmButtonColor:"#c9a96e",cancelButtonColor:"#f44336",confirmButtonText:"Yes, logout"}).then(e=>{if(e.isConfirmed){const n=document.createElement("form");n.method="POST",n.action="/logout";const t=document.querySelector('meta[name="csrf-token"]');if(t){const i=document.createElement("input");i.type="hidden",i.name="_token",i.value=t.getAttribute("content"),n.appendChild(i)}document.body.appendChild(n),n.submit()}})}document.querySelectorAll(".menu-item").forEach(e=>{e.addEventListener("click",function(n){this.getAttribute("href")==="#"&&n.preventDefault(),(!this.onclick||this.getAttribute("href")==="#")&&(document.querySelectorAll(".menu-item").forEach(t=>t.classList.remove("active")),this.classList.add("active"))})});document.addEventListener("click",function(e){const n=document.getElementById("sidebar"),t=document.querySelector(".mobile-toggle");window.innerWidth<=768&&!n.contains(e.target)&&!t.contains(e.target)&&n.classList.remove("active")});typeof $=="function"&&(window.toggleSidebar=$);typeof P=="function"&&(window.toggleNotifications=P);typeof z=="function"&&(window.switchNotificationTab=z);typeof j=="function"&&(window.markAllRead=j);typeof D=="function"&&(window.openComposeMessage=D);typeof Q=="function"&&(window.viewProfile=Q);typeof K=="function"&&(window.openSettings=K);typeof W=="function"&&(window.logout=W);typeof X=="function"&&(window.uploadPhotos=X);typeof b=="function"&&(window.openGallery=b);typeof O=="function"&&(window.closeGallery=O);typeof q=="function"&&(window.triggerFileInput=q);typeof C=="function"&&(window.handleFileSelect=C);typeof V=="function"&&(window.selectAll=V);typeof J=="function"&&(window.deleteSelected=J);typeof Y=="function"&&(window.shareSelected=Y);typeof F=="function"&&(window.viewMedia=F);typeof B=="function"&&(window.closeViewer=B);typeof R=="function"&&(window.editMedia=R);typeof _=="function"&&(window.downloadMedia=_);typeof H=="function"&&(window.shareMedia=H);typeof G=="function"&&(window.deleteMedia=G);
