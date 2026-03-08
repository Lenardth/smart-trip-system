function getMoodText(m) {
    return {
        adventurous:'Adventurous 🏔️', relaxed:'Relaxed 🌴', cultural:'Cultural 🏛️',
        romantic:'Romantic 💖', foodie:'Foodie 🍽️', wellness:'Wellness 🧘',
        nightlife:'Nightlife 🎉', nature:'Nature 🌿'
    }[m] || m;
}
function getBudgetText(b) {
    return {
        backpacker:'Backpacker 🎒', budget:'Budget 💰', mid:'Mid-range 💵',
        premium:'Premium 💳', luxury:'Luxury 💎'
    }[b] || b;
}
function getDurationText(d) {
    return {
        weekend:'Long Weekend', week:'One Week', two_weeks:'Two Weeks',
        month:'One Month+', flexible:'Flexible'
    }[d] || d;
}
function getCompanionText(c) {
    return {
        solo:'Solo 🧍', couple:'Couple 👫', family_young:'Family (young kids) 👨‍👩‍👧',
        family_teens:'Family (teens) 👨‍👩‍👦', friends_small:'Small group 👯',
        friends_large:'Large group 🎊', business:'Business 💼'
    }[c] || c;
}
function getRegionText(r) {
    return {
        any:'Anywhere 🌍', europe:'Europe', southeast_asia:'Southeast Asia',
        east_asia:'East Asia', south_asia:'South Asia', middle_east:'Middle East',
        africa:'Africa', north_america:'North America', central_america:'Central America & Caribbean',
        south_america:'South America', oceania:'Oceania'
    }[r] || r;
}

function getVal(id) {
    const el = document.getElementById(id);
    return el ? el.value : '';
}

async function generateQuickPlan(e) {
    const mood          = getVal('moodSelect');
    const budget        = getVal('budgetSelect');
    const duration      = getVal('durationSelect');
    const companion     = getVal('companionSelect');
    const month         = getVal('monthSelect');
    const region        = getVal('regionSelect');
    const accommodation = getVal('accommodationSelect');
    const origin        = getVal('originSelect');
    const experience    = getVal('experienceSelect');

    const button       = e.currentTarget;
    const originalHTML = button.innerHTML;
    button.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Consulting AI...';
    button.disabled    = true;

    const modal = document.createElement('div');
    modal.id = 'aiSuggestionModal';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.82);display:flex;justify-content:center;align-items:center;z-index:9999;';

    const modalContent = document.createElement('div');
    modalContent.style.cssText = 'background:var(--card-bg);padding:40px;border-radius:10px;max-width:660px;width:90%;max-height:88vh;overflow-y:auto;position:relative;border:2px solid var(--gold);box-shadow:0 20px 60px rgba(59,31,43,0.35);';

    modalContent.innerHTML = `
        <h2 style="color:var(--deep);margin-top:0;font-weight:normal;letter-spacing:1px;">
            <i class="fas fa-compass" style="color:var(--gold);margin-right:10px;"></i>Finding Your Perfect Trip…
        </h2>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getMoodText(mood)}</span>
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getBudgetText(budget)}</span>
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getDurationText(duration)}</span>
            <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">${getCompanionText(companion)}</span>
            ${month ? `<span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">📅 ${month.charAt(0).toUpperCase()+month.slice(1)}</span>` : ''}
            ${region && region !== 'any' ? `<span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid var(--border);">📍 ${getRegionText(region)}</span>` : ''}
        </div>
        <div style="text-align:center;padding:40px 0;">
            <i class="fas fa-globe-americas fa-3x fa-spin" style="color:var(--gold);opacity:0.7;"></i>
            <p style="color:var(--text-muted);margin-top:20px;">Our AI is crafting personalised recommendations just for you…</p>
        </div>`;

    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    modal.addEventListener('click', (ev) => { if (ev.target === modal) modal.remove(); });

    try {
        const response = await fetch('/ai/suggest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ mood, budget, duration, companion, month, region, accommodation, origin, experience }),
        });

        const json = await response.json();
        if (!response.ok || !json.success) throw new Error(json.message ?? 'Unknown error');

        const suggestions = Array.isArray(json.data) ? json.data : [json.data];
        const flags = ['🌏','🌍','🌎','🌐','🗺️'];

        const cards = suggestions.map((s, i) => {
            const activities = Array.isArray(s.top_activities) ? s.top_activities.join(', ') : s.top_activities;
            const slug = (s.destination||'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');
            return `
            <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:16px;">
                <div style="background:linear-gradient(135deg,var(--deep),var(--deep-alt));padding:18px 22px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="color:var(--gold);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Option ${i+1} ${flags[i]||'✈️'}</span>
                        ${s.country ? `<span style="background:rgba(201,169,110,0.2);color:var(--gold);padding:3px 10px;border-radius:20px;font-size:12px;">${s.country}</span>` : ''}
                    </div>
                    <h3 style="color:var(--text-light);margin:0 0 6px;font-size:19px;font-weight:normal;">${s.destination}</h3>
                    <p style="color:var(--text-sub);margin:0;font-size:13px;line-height:1.6;">${s.description}</p>
                </div>
                <div style="padding:14px 22px;background:var(--card-bg);">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px;">
                        <div>
                            <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Est. Cost</p>
                            <p style="color:var(--deep);margin:0;font-size:13px;font-weight:bold;">${s.estimated_cost}</p>
                        </div>
                        <div>
                            <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Best Time</p>
                            <p style="color:var(--deep);margin:0;font-size:13px;">${s.best_time_to_visit}</p>
                        </div>
                        <div>
                            <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">Visa</p>
                            <p style="color:var(--deep);margin:0;font-size:13px;">${s.visa_info || 'Check embassy'}</p>
                        </div>
                    </div>
                    <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 4px;">Top Activities</p>
                    <p style="color:var(--text-muted);margin:0 0 10px;font-size:13px;">${activities}</p>
                    ${s.flight_info ? `
                    <div style="background:rgba(201,169,110,0.08);border-radius:4px;padding:8px 12px;margin-bottom:10px;border:1px solid rgba(201,169,110,0.2);">
                        <p style="color:var(--gold);font-size:10px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 2px;">✈️ Flight Info from ${origin.replace('_',' ')}</p>
                        <p style="color:var(--text-muted);margin:0;font-size:12px;">${s.flight_info}</p>
                    </div>` : ''}
                    <div style="border-left:2px solid var(--gold);padding-left:10px;margin-bottom:12px;">
                        <p style="color:var(--text-muted);margin:0;font-size:12px;font-style:italic;">💡 ${s.travel_tip}</p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="/flights?destination=${encodeURIComponent(slug)}&origin=${encodeURIComponent(origin)}&mood=${mood}&budget=${budget}"
                            class="primary-button"
                            style="flex:1;font-size:12px;padding:9px;background:var(--deep);color:var(--text-light);text-decoration:none;justify-content:center;">
                            <i class="fas fa-plane"></i> Search Flights
                        </a>
                        <a href="/plan-trip?destination=${encodeURIComponent(s.destination)}&mood=${mood}&budget=${budget}&duration=${duration}&companion=${companion}&month=${month}&accommodation=${accommodation}"
                            class="primary-button"
                            style="flex:1;font-size:12px;padding:9px;justify-content:center;text-decoration:none;">
                            <i class="fas fa-map"></i> Plan Trip
                        </a>
                    </div>
                </div>
            </div>`;
        }).join('');

        modalContent.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                <h2 style="color:var(--deep);margin:0;font-weight:normal;letter-spacing:1px;">
                    <i class="fas fa-globe" style="color:var(--gold);margin-right:10px;"></i>Your AI Travel Matches
                </h2>
                <button onclick="document.getElementById('aiSuggestionModal').remove()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-muted);line-height:1;">&times;</button>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;">
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getMoodText(mood)}</span>
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getBudgetText(budget)}</span>
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getDurationText(duration)}</span>
                <span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">${getCompanionText(companion)}</span>
                ${month ? `<span style="background:rgba(201,169,110,0.15);color:var(--deep);padding:3px 10px;border-radius:20px;font-size:11px;border:1px solid var(--border);">📅 ${month.charAt(0).toUpperCase()+month.slice(1)}</span>` : ''}
            </div>
            ${cards}
            <button class="primary-button"
                onclick="document.getElementById('aiSuggestionModal').remove()"
                style="width:100%;background:var(--card-bg);color:var(--deep);border:1px solid var(--border);margin-top:4px;">
                Close
            </button>`;

    } catch (err) {
        modalContent.innerHTML = `
            <h2 style="color:var(--deep);margin-top:0;">Something went wrong</h2>
            <p style="color:var(--text-muted);">${err.message || 'Unable to fetch suggestions right now. Please try again.'}</p>
            <button class="primary-button" onclick="document.getElementById('aiSuggestionModal').remove()">Close</button>`;
    } finally {
        button.innerHTML = originalHTML;
        button.disabled  = false;
    }
}

function subscribeNewsletter() {
    const emailInput = document.querySelector('.newsletter-input input');
    if (!emailInput) return;
    const email = emailInput.value;
    if (!email || !email.includes('@')) { alert('Please enter a valid email address'); return; }

    const btn = document.querySelector('.newsletter-input button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
    btn.style.background = '#6b8f6b';
    btn.disabled = true;

    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
        btn.disabled = false;
        emailInput.value = '';
        const msg = document.createElement('div');
        msg.textContent = 'Thank you for subscribing! Check your email for confirmation.';
        msg.style.cssText = 'position:fixed;bottom:20px;right:20px;background:var(--deep);color:var(--text-light);padding:15px 25px;border-radius:5px;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {

    const generateBtn = document.getElementById('generatePlanBtn');
    if (generateBtn) generateBtn.addEventListener('click', generateQuickPlan);

    document.querySelectorAll('.qb-next-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const nextStep = parseInt(this.getAttribute('data-next'));
            showStep(nextStep);
        });
    });

    document.querySelectorAll('.qb-back-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const backStep = parseInt(this.getAttribute('data-back'));
            showStep(backStep);
        });
    });

    function showStep(step) {
        [1, 2, 3].forEach(n => {
            const panel = document.getElementById(`qbPanel${n}`);
            const stepEl = document.querySelector(`.qb-step[data-step="${n}"]`);
            const lines = document.querySelectorAll('.qb-step-line');
            if (panel) panel.style.display = n === step ? 'block' : 'none';
            if (stepEl) {
                stepEl.classList.toggle('active', n === step);
                stepEl.classList.toggle('done', n < step);
            }
            if (lines[n - 1]) lines[n - 1].classList.toggle('done', n < step);
        });
    }

    const nextBtn = document.querySelector('.next-btn');
    const prevBtn = document.querySelector('.prev-btn');
    const slideshowContainer = document.querySelector('.slideshow-container');
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const slideNumber = document.querySelector('.slide-number');
    const totalSlides = slides.length;

    if (totalSlides > 0 && nextBtn && prevBtn && slideshowContainer) {
        let currentSlide = 0;
        let nextSlideIndex = 0;
        let isTransitioning = false;
        let slideInterval;
        let slideshowDirection = 1;

        function updateSlide(immediate = false) {
            if (isTransitioning) return;
            isTransitioning = true;
            slides.forEach(s => s.classList.remove('active', 'exiting'));
            if (!immediate && slides[currentSlide]) slides[currentSlide].classList.add('exiting');
            indicators.forEach(ind => ind.classList.remove('active'));
            setTimeout(() => {
                if (slides[currentSlide]) slides[currentSlide].classList.remove('exiting');
                slides[nextSlideIndex].classList.add('active');
                if (indicators[nextSlideIndex]) indicators[nextSlideIndex].classList.add('active');
                if (slideNumber) slideNumber.textContent = `${nextSlideIndex + 1} / ${totalSlides}`;
                currentSlide = nextSlideIndex;
                setTimeout(() => { isTransitioning = false; }, 1200);
            }, immediate ? 0 : 300);
        }

        function nextSlide() {
            if (isTransitioning) return;
            slideshowDirection = 1;
            nextSlideIndex = (currentSlide + 1) % totalSlides;
            updateSlide();
        }

        function prevSlide() {
            if (isTransitioning) return;
            slideshowDirection = -1;
            nextSlideIndex = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide();
        }

        function startAutoSlide() {
            clearInterval(slideInterval);
            slideInterval = setInterval(() => {
                slideshowDirection === 1 ? nextSlide() : prevSlide();
                if (Math.random() < 0.1) slideshowDirection *= -1;
            }, 6000);
        }

        function stopAutoSlide() { clearInterval(slideInterval); }

        function goToSlide(index) {
            if (isTransitioning || index === currentSlide) return;
            slideshowDirection = index > currentSlide ? 1 : -1;
            nextSlideIndex = index;
            updateSlide();
        }

        nextBtn.addEventListener('click', () => { slideshowDirection = 1; nextSlide(); startAutoSlide(); });
        prevBtn.addEventListener('click', () => { slideshowDirection = -1; prevSlide(); startAutoSlide(); });

        indicators.forEach(indicator => {
            indicator.addEventListener('click', function () {
                goToSlide(parseInt(this.getAttribute('data-slide')));
                startAutoSlide();
            });
        });

        slideshowContainer.addEventListener('mouseenter', stopAutoSlide);
        slideshowContainer.addEventListener('mouseleave', startAutoSlide);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft')  { prevSlide(); startAutoSlide(); }
            if (e.key === 'ArrowRight') { nextSlide(); startAutoSlide(); }
        });

        updateSlide(true);
        startAutoSlide();
    }

    document.querySelectorAll('.filter-tag').forEach(tag => {
        tag.addEventListener('click', function () {
            document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.textContent.trim().toLowerCase();
            const cards = document.querySelectorAll('.destination-card');
            cards.forEach(card => { card.style.opacity = '0.5'; card.style.transform = 'scale(0.95)'; });
            setTimeout(() => {
                cards.forEach(card => {
                    const title = card.querySelector('h3')?.textContent.toLowerCase() ?? '';
                    const mood  = card.querySelector('.mood-indicator')?.textContent.toLowerCase() ?? '';
                    const price = card.querySelector('.price-tag')?.textContent.toLowerCase() ?? '';
                    const ok = filter === 'all' || title.includes(filter) || mood.includes(filter) || price.includes(filter);
                    card.style.display   = ok ? 'flex' : 'none';
                    card.style.opacity   = ok ? '1' : '0.5';
                    card.style.transform = ok ? 'scale(1)' : 'scale(0.95)';
                });
            }, 300);
        });
    });

    document.querySelectorAll('.destination-card').forEach(card => {
        card.addEventListener('mouseenter', function () { this.style.transform='translateY(-5px) scale(1.02)'; this.style.boxShadow='0 8px 22px rgba(59,31,43,0.15)'; });
        card.addEventListener('mouseleave', function () { this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; });
    });

    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mouseenter', function () { this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 22px rgba(59,31,43,0.15)'; });
        card.addEventListener('mouseleave', function () { this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; });
    });

    document.querySelectorAll('.tile').forEach(tile => {
        tile.addEventListener('mouseenter', function () { this.style.transform='translateY(-5px)'; this.style.boxShadow='0 6px 18px rgba(59,31,43,0.13)'; });
        tile.addEventListener('mouseleave', function () { this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                document.querySelectorAll('.stat-number').forEach((stat, i) => {
                    setTimeout(() => {
                        stat.style.transform = 'scale(1.1)';
                        setTimeout(() => { stat.style.transform = 'scale(1)'; }, 300);
                    }, i * 200);
                });
            }
        });
    }, { threshold: 0.5 });

    const aiBanner = document.querySelector('.ai-features-banner');
    if (aiBanner) observer.observe(aiBanner);

    document.querySelectorAll('.testimonial-card').forEach(card => {
        card.addEventListener('click', function () {
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(59,31,43,0.15)';
            setTimeout(() => { this.style.transform='scale(1)'; this.style.boxShadow='0 3px 10px rgba(59,31,43,0.08)'; }, 200);
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (ev) {
            ev.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const target = document.querySelector(targetId);
            if (target) window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
        });
    });

    const footerP = document.querySelector('.footer p');
    if (footerP) footerP.innerHTML = footerP.innerHTML.replace('2026', new Date().getFullYear());
});
