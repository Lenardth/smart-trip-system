@extends('layouts.public')

@section('title', 'Smart booking')

@push('styles')
    @vite(['resources/css/blade/landing/index.css'])
@endpush

@push('scripts_body')
    @vite(['resources/js/blade/landing/index.js'])
@endpush

@section('content')
<section class="hero" style="background: linear-gradient(rgba(30, 15, 20, .6), rgba(30, 15, 20, .6)), url('https://images.pexels.com/photos/414171/pexels-photo-414171.jpeg'); background-size: cover; background-position: center;">
    <div class="hero-content">
        <h1>Plan Your Perfect Journey with AI</h1>
        <p>Personalized travel recommendations based on your mood, preferences, and budget. Discover destinations you'll love.</p>
        <div class="hero-buttons">
            <button class="primary-button" onclick="window.location.href='/plan-trip'">
                <i class="fas fa-magic"></i> Start Planning Now
            </button>
            <button class="secondary-button" onclick="window.location.href='/flights'">
                <i class="fas fa-plane"></i> Book Flights
            </button>
        </div>
    </div>
</section>

<div class="quick-plan">
    <h2 class="section-title">Quick Trip Builder</h2>
    <p class="section-subtitle">Tell us about your ideal trip and our AI will find the perfect destinations for you</p>

    <div class="qb-steps">
        <div class="qb-step active" data-step="1">
            <span class="qb-step-num">1</span>
            <span class="qb-step-label">Who & Mood</span>
        </div>
        <div class="qb-step-line"></div>
        <div class="qb-step" data-step="2">
            <span class="qb-step-num">2</span>
            <span class="qb-step-label">Trip Details</span>
        </div>
        <div class="qb-step-line"></div>
        <div class="qb-step" data-step="3">
            <span class="qb-step-num">3</span>
            <span class="qb-step-label">Preferences</span>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel1">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-users"></i> Travelling With</label>
                <select id="companionSelect" name="companion">
                    <option value="solo">Solo — Just me</option>
                    <option value="couple">Couple — Partner & I</option>
                    <option value="family_young">Family — Young children (under 12)</option>
                    <option value="family_teens">Family — Teenagers</option>
                    <option value="friends_small">Friends — Small group (2–4)</option>
                    <option value="friends_large">Friends — Large group (5+)</option>
                    <option value="business">Business Travel</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-heart"></i> Travel Mood</label>
                <div class="custom-select-wrapper" id="moodSelectWrapper">
                    <div class="custom-select-trigger" id="moodSelectTrigger">
                        <span class="custom-select-icon"><i class="fas fa-hiking"></i></span>
                        <span class="custom-select-text">Adventurous — Thrills & outdoors</span>
                        <i class="fas fa-chevron-down custom-select-arrow"></i>
                    </div>
                    <div class="custom-select-dropdown" id="moodDropdown" style="display:none;">
                        <div class="custom-select-group">Popular Moods</div>
                        <div class="custom-select-option selected" data-value="adventurous"><i class="fas fa-hiking"></i> Adventurous — Thrills &amp; outdoors</div>
                        <div class="custom-select-option" data-value="relaxed"><i class="fas fa-umbrella-beach"></i> Relaxed — Rest &amp; recharge</div>
                        <div class="custom-select-option" data-value="romantic"><i class="fas fa-heart"></i> Romantic — Intimate &amp; scenic</div>
                        <div class="custom-select-option" data-value="cultural"><i class="fas fa-landmark"></i> Cultural — History &amp; arts</div>
                        <div class="custom-select-option" data-value="foodie"><i class="fas fa-utensils"></i> Foodie — Cuisine &amp; markets</div>
                        <div class="custom-select-option" data-value="nature"><i class="fas fa-tree"></i> Nature — Wildlife &amp; eco-tourism</div>

                        <div class="custom-select-group">Mindset &amp; Wellness</div>
                        <div class="custom-select-option" data-value="wellness"><i class="fas fa-spa"></i> Wellness — Spa &amp; mindfulness</div>
                        <div class="custom-select-option" data-value="spiritual"><i class="fas fa-place-of-worship"></i> Spiritual — Temples &amp; retreats</div>
                        <div class="custom-select-option" data-value="digital_detox"><i class="fas fa-power-off"></i> Digital Detox — Unplug &amp; reconnect</div>
                        <div class="custom-select-option" data-value="slow_travel"><i class="fas fa-feather"></i> Slow Travel — Immerse, don't rush</div>
                        <div class="custom-select-option" data-value="healing"><i class="fas fa-hand-holding-heart"></i> Healing — Recovery &amp; self-care</div>
                        <div class="custom-select-option" data-value="solo_discovery"><i class="fas fa-user"></i> Solo Discovery — Find yourself</div>

                        <div class="custom-select-group">Social &amp; Entertainment</div>
                        <div class="custom-select-option" data-value="nightlife"><i class="fas fa-music"></i> Nightlife — Music &amp; entertainment</div>
                        <div class="custom-select-option" data-value="festival"><i class="fas fa-star"></i> Festival Chaser — Events &amp; carnivals</div>
                        <div class="custom-select-option" data-value="sports"><i class="fas fa-futbol"></i> Sports Fan — Matches &amp; stadiums</div>
                        <div class="custom-select-option" data-value="party"><i class="fas fa-glass-cheers"></i> Party Mode — Celebrate &amp; socialise</div>
                        <div class="custom-select-option" data-value="lgbtq"><i class="fas fa-rainbow"></i> LGBTQ+ Friendly — Inclusive destinations</div>
                        <div class="custom-select-option" data-value="social"><i class="fas fa-users"></i> Social Butterfly — Meet new people</div>

                        <div class="custom-select-group">Exploration Styles</div>
                        <div class="custom-select-option" data-value="road_trip"><i class="fas fa-car"></i> Road Trip — Open roads &amp; freedom</div>
                        <div class="custom-select-option" data-value="backpacking"><i class="fas fa-suitcase"></i> Backpacking — Rough it &amp; explore</div>
                        <div class="custom-select-option" data-value="island_hopping"><i class="fas fa-water"></i> Island Hopping — Sea &amp; archipelagos</div>
                        <div class="custom-select-option" data-value="city_break"><i class="fas fa-city"></i> City Break — Urban buzz &amp; skylines</div>
                        <div class="custom-select-option" data-value="off_beaten"><i class="fas fa-map"></i> Off the Beaten Path — Hidden gems</div>
                        <div class="custom-select-option" data-value="luxury_escape"><i class="fas fa-gem"></i> Luxury Escape — Five-star everything</div>
                        <div class="custom-select-option" data-value="safari"><i class="fas fa-paw"></i> Safari &amp; Wildlife — Raw nature up close</div>
                        <div class="custom-select-option" data-value="cruise"><i class="fas fa-ship"></i> Cruise — Sea views &amp; port hopping</div>

                        <div class="custom-select-group">Seasonal &amp; Weather</div>
                        <div class="custom-select-option" data-value="sun_seeker"><i class="fas fa-sun"></i> Sun Seeker — Heat &amp; beaches</div>
                        <div class="custom-select-option" data-value="snow_lover"><i class="fas fa-snowflake"></i> Snow Lover — Skiing &amp; winter wonderlands</div>
                        <div class="custom-select-option" data-value="autumn_vibes"><i class="fas fa-leaf"></i> Autumn Vibes — Foliage &amp; cosy escapes</div>
                        <div class="custom-select-option" data-value="monsoon_magic"><i class="fas fa-cloud-rain"></i> Monsoon Magic — Lush greens &amp; rain</div>
                        <div class="custom-select-option" data-value="northern_lights"><i class="fas fa-moon"></i> Aurora Chaser — Northern lights</div>

                        <div class="custom-select-group">Learning &amp; Growth</div>
                        <div class="custom-select-option" data-value="language"><i class="fas fa-language"></i> Language Immersion — Learn while travelling</div>
                        <div class="custom-select-option" data-value="photography"><i class="fas fa-camera"></i> Photography Trip — Capture stunning moments</div>
                        <div class="custom-select-option" data-value="volunteer"><i class="fas fa-hands-helping"></i> Volunteer Travel — Give back while exploring</div>
                        <div class="custom-select-option" data-value="study_abroad"><i class="fas fa-graduation-cap"></i> Study Abroad — Education &amp; experience</div>
                        <div class="custom-select-option" data-value="cooking_class"><i class="fas fa-utensils"></i> Culinary School — Cook like a local</div>
                        <div class="custom-select-option" data-value="art_retreat"><i class="fas fa-paint-brush"></i> Art Retreat — Paint, sculpt &amp; create</div>

                        <div class="custom-select-group">Family &amp; Groups</div>
                        <div class="custom-select-option" data-value="family_fun"><i class="fas fa-child"></i> Family Fun — Kids activities &amp; theme parks</div>
                        <div class="custom-select-option" data-value="multigenerational"><i class="fas fa-users"></i> Multi-Generational — Everyone included</div>
                        <div class="custom-select-option" data-value="honeymoon"><i class="fas fa-ring"></i> Honeymoon — Perfect romantic start</div>
                        <div class="custom-select-option" data-value="girls_trip"><i class="fas fa-female"></i> Girls Trip — Friendship &amp; fun</div>
                        <div class="custom-select-option" data-value="boys_trip"><i class="fas fa-male"></i> Boys Trip — Adventure &amp; bonding</div>
                        <div class="custom-select-option" data-value="group_adventure"><i class="fas fa-mountain"></i> Group Adventure — Shared thrills</div>
                    </div>
                    <input type="hidden" id="moodSelect" name="mood" value="adventurous">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-star"></i> Experience Level</label>
                <select id="experienceSelect" name="experience">
                    <option value="first_time">First-time traveller</option>
                    <option value="occasional">Occasional traveller (1–2 trips/year)</option>
                    <option value="regular">Regular traveller (3–5 trips/year)</option>
                    <option value="frequent">Frequent traveller (6+ trips/year)</option>
                </select>
            </div>
        </div>
        <div class="qb-nav">
            <button class="primary-button qb-next-btn" data-next="2">
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel2" style="display:none;">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Departure Month</label>
                <select id="monthSelect" name="month">
                    <option value="january">January</option>
                    <option value="february">February</option>
                    <option value="march">March</option>
                    <option value="april">April</option>
                    <option value="may">May</option>
                    <option value="june">June</option>
                    <option value="july">July</option>
                    <option value="august">August</option>
                    <option value="september">September</option>
                    <option value="october">October</option>
                    <option value="november">November</option>
                    <option value="december">December</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-clock"></i> Trip Duration</label>
                <select id="durationSelect" name="duration">
                    <option value="weekend">Long Weekend (3–4 days)</option>
                    <option value="week">One Week (5–7 days)</option>
                    <option value="two_weeks">Two Weeks (10–14 days)</option>
                    <option value="month">One Month+</option>
                    <option value="flexible">Flexible / Open-ended</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-dollar-sign"></i> Budget Per Person</label>
                <select id="budgetSelect" name="budget">
                    <option value="backpacker">Backpacker — Under $500</option>
                    <option value="budget">Budget — $500–$1,500</option>
                    <option value="mid">Mid-range — $1,500–$4,000</option>
                    <option value="premium">Premium — $4,000–$8,000</option>
                    <option value="luxury">Luxury — $8,000+</option>
                </select>
            </div>
        </div>
        <div class="qb-nav">
            <button class="secondary-button qb-back-btn" data-back="1">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="primary-button qb-next-btn" data-next="3">
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <div class="qb-panel" id="qbPanel3" style="display:none;">
        <div class="quick-plan-form">
            <div class="form-group">
                <label><i class="fas fa-globe"></i> Preferred Region</label>
                <select id="regionSelect" name="region">
                    <option value="any">Anywhere — Surprise me!</option>
                    <option value="europe">Europe</option>
                    <option value="southeast_asia">Southeast Asia</option>
                    <option value="east_asia">East Asia</option>
                    <option value="south_asia">South Asia</option>
                    <option value="middle_east">Middle East</option>
                    <option value="africa">Africa</option>
                    <option value="north_america">North America</option>
                    <option value="central_america">Central America &amp; Caribbean</option>
                    <option value="south_america">South America</option>
                    <option value="oceania">Oceania &amp; Pacific</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-bed"></i> Accommodation Style</label>
                <select id="accommodationSelect" name="accommodation">
                    <option value="any">No preference</option>
                    <option value="hostel">Hostel / Budget guesthouse</option>
                    <option value="bnb">B&amp;B / Boutique hotel</option>
                    <option value="hotel">3–4 star hotel</option>
                    <option value="resort">5-star resort</option>
                    <option value="villa">Private villa / Airbnb</option>
                    <option value="glamping">Glamping / Eco-lodge</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-plane-departure"></i> Flying From</label>
                <input type="text" id="originInput" name="origin" placeholder="e.g. Johannesburg, London, New York, Dubai..." autocomplete="off">
                <small class="input-hint"><i class="fas fa-info-circle"></i> Enter any city or airport name</small>
            </div>
        </div>
        <div class="qb-nav">
            <button class="secondary-button qb-back-btn" data-back="2">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="primary-button" id="generatePlanBtn">
                <i class="fas fa-robot"></i> Generate AI Suggestions
            </button>
        </div>
    </div>
</div>

<div id="aiRecommendations" class="ai-recommendations" style="display: none;">
    <div class="loading-spinner" id="loadingSpinner" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i> AI is analyzing your preferences...
    </div>
    <div id="recommendationsContent"></div>
</div>

<div class="tile-grid">
    <div class="tile">
        <h3><i class="fas fa-plane"></i> Easy Flight Booking</h3>
        <p>Search and book flights worldwide with our integrated flight booking system. Get the best deals on airfare.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-brain"></i> AI Mood-Based Suggestions</h3>
        <p>Tell us how you feel—adventurous, relaxed, cultural—and get personalized destination recommendations.</p>
    </div>
    <div class="tile">
        <h3><i class="fas fa-sliders-h"></i> Smart Budget Optimization</h3>
        <p>Set your budget and let our algorithm find the best flights, accommodations, and activities within your range.</p>
    </div>
</div>

<section class="slideshow-section">
    <h2 class="section-title">Featured Destinations</h2>
    <p class="section-subtitle">Discover handpicked destinations curated by our travel experts</p>
    <div class="slideshow-container">
        <div class="slides">
            <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1516496636080-14fb876e029d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Amalfi Coast, Italy</h3>
                    <p>Experience the breathtaking beauty of Italy's coastline with its colorful cliffside villages, delicious cuisine, and Mediterranean charm.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963?ixlib=rb-4.0.3&auto=format&fit=crop&w=2067&q=80');">
                <div class="slide-content">
                    <h3>Bali, Indonesia</h3>
                    <p>Find your inner peace in Bali's spiritual retreats, lush rice terraces, and pristine beaches. Perfect for relaxation and adventure.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Santorini, Greece</h3>
                    <p>Marvel at the iconic white-washed buildings, stunning sunsets, and crystal-clear waters of this romantic Greek island paradise.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2064&q=80');">
                <div class="slide-content">
                    <h3>Kyoto, Japan</h3>
                    <p>Step back in time with ancient temples, traditional tea houses, and the magical beauty of cherry blossom season in Kyoto.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1579530190412-b35a65e17c8d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2066&q=80');">
                <div class="slide-content">
                    <h3>Swiss Alps</h3>
                    <p>Embrace adventure in the majestic Swiss Alps with breathtaking mountain views, skiing, and luxury mountain resorts.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1513326738677-b964603b136d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Marrakech, Morocco</h3>
                    <p>Discover vibrant souks, stunning palaces, and rich cultural heritage in this enchanting North African city.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>Maldives Overwater Bungalows</h3>
                    <p>Experience ultimate luxury in crystal-clear turquoise waters with private villas and world-class diving.</p>
                </div>
            </div>
            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1543832923-44667a44c804?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                <div class="slide-content">
                    <h3>New York City, USA</h3>
                    <p>Explore the city that never sleeps with iconic landmarks, Broadway shows, and diverse culinary experiences.</p>
                </div>
            </div>
        </div>
        <div class="slide-number">1 / 8</div>
        <div class="slide-controls">
            <button class="slide-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="slide-btn next-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="slide-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
            <span class="indicator" data-slide="3"></span>
            <span class="indicator" data-slide="4"></span>
            <span class="indicator" data-slide="5"></span>
            <span class="indicator" data-slide="6"></span>
            <span class="indicator" data-slide="7"></span>
        </div>
    </div>
</section>

<section class="discover-section">
    <div class="discover-header">
        <h2 class="section-title">Discover Trending Destinations</h2>
        <button class="secondary-button" onclick="window.location.href='/discover'">View All <i class="fas fa-arrow-right"></i></button>
    </div>
    <div class="filter-tags">
        <span class="filter-tag active" data-filter="all"><i class="fas fa-globe"></i> All</span>
        <span class="filter-tag" data-filter="trending"><i class="fas fa-fire"></i> Trending</span>
        <span class="filter-tag" data-filter="beach"><i class="fas fa-umbrella-beach"></i> Beach</span>
        <span class="filter-tag" data-filter="mountain"><i class="fas fa-mountain"></i> Mountain</span>
        <span class="filter-tag" data-filter="historical"><i class="fas fa-landmark"></i> Historical</span>
        <span class="filter-tag" data-filter="food_culture"><i class="fas fa-utensils"></i> Food & Culture</span>
        <span class="filter-tag" data-filter="eco_tourism"><i class="fas fa-leaf"></i> Eco-Tourism</span>
        <span class="filter-tag" data-filter="romantic"><i class="fas fa-heart"></i> Romantic</span>
        <span class="filter-tag" data-filter="adventurous"><i class="fas fa-hiking"></i> Adventure</span>
        <span class="filter-tag" data-filter="hidden_gem"><i class="fas fa-gem"></i> Hidden Gems</span>
    </div>
    <div id="destinationsLoading" style="text-align:center; padding: 60px 20px;">
        <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
        <p>Loading destinations...</p>
    </div>
    <div class="destinations-grid" id="destinationsGrid" style="display:none;"></div>
    <div id="destinationsEmpty" style="display:none; text-align:center; padding:60px 20px;">
        <i class="fas fa-map-marked-alt" style="font-size:40px; opacity:0.3;"></i>
        <p>No destinations found for this filter.</p>
    </div>
</section>

<div style="max-width: 1200px; margin: 60px auto; padding: 40px; background: #fff; border-radius: 6px; border: 1px solid #e0e0e0;">
    <h2 class="section-title">Ready to Fly?</h2>
    <p class="section-subtitle">Find and book flights to your dream destinations</p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px;">
        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid #e0e0e0; cursor: pointer;" onclick="window.location.href='/flights'">
            <div style="font-size: 48px; color: #2c3e50; margin-bottom: 20px;"><i class="fas fa-search"></i></div>
            <h3 style="color: #2c3e50; margin-bottom: 10px;">Search Flights</h3>
            <p style="color: #666;">Find flights worldwide with flexible dates</p>
        </div>
        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid #e0e0e0; cursor: pointer;" onclick="window.location.href='/flights/create'">
            <div style="font-size: 48px; color: #2c3e50; margin-bottom: 20px;"><i class="fas fa-plus-circle"></i></div>
            <h3 style="color: #2c3e50; margin-bottom: 10px;">Create Flight</h3>
            <p style="color: #666;">Add custom flight options</p>
        </div>
        <div style="text-align: center; padding: 30px; background: rgba(201,169,110,0.1); border-radius: 6px; border: 1px solid #e0e0e0; cursor: pointer;" onclick="window.location.href='/bookings'">
            <div style="font-size: 48px; color: #2c3e50; margin-bottom: 20px;"><i class="fas fa-ticket-alt"></i></div>
            <h3 style="color: #2c3e50; margin-bottom: 10px;">My Bookings</h3>
            <p style="color: #666;">View and manage your bookings</p>
        </div>
    </div>
    <div style="text-align: center; margin-top: 40px;">
        <button class="primary-button" onclick="window.location.href='/flights'" style="padding: 15px 40px; font-size: 16px;">
            <i class="fas fa-plane"></i> Start Booking Flights Now
        </button>
    </div>
</div>

<div style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
    <h2 class="section-title">Explore By Travel Style</h2>
    <p class="section-subtitle">Find destinations that match your preferred travel experience</p>
    <div class="explore-categories">
        <div class="category-card active-style" data-style="adventure" onclick="filterByStyle('adventure', this)">
            <div class="category-icon"><i class="fas fa-hiking"></i></div>
            <h3>Adventure Travel</h3>
            <p>Hiking, trekking, and extreme sports destinations</p>
        </div>
        <div class="category-card" data-style="beach" onclick="filterByStyle('beach', this)">
            <div class="category-icon"><i class="fas fa-umbrella-beach"></i></div>
            <h3>Beach &amp; Relaxation</h3>
            <p>Perfect spots for sunbathing and unwinding</p>
        </div>
        <div class="category-card" data-style="cultural" onclick="filterByStyle('cultural', this)">
            <div class="category-icon"><i class="fas fa-landmark"></i></div>
            <h3>Cultural Immersion</h3>
            <p>Historical sites and cultural experiences</p>
        </div>
        <div class="category-card" data-style="food" onclick="filterByStyle('food', this)">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <h3>Culinary Tours</h3>
            <p>Foodie paradises and cooking experiences</p>
        </div>
    </div>
    <div id="styleResultsHeader" style="display:none; margin: 30px 0 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3 id="styleResultsTitle" style="margin:0; font-size:18px;"></h3>
            <span id="styleResultsCount" style="font-size:13px;"></span>
        </div>
    </div>
    <div class="destinations-grid" id="styleDestinationsGrid" style="display:none; margin-top:24px;"></div>
    <div id="styleEmpty" style="display:none; text-align:center; padding:40px;">
        <i class="fas fa-map-marked-alt" style="font-size:36px; opacity:0.3;"></i>
        <p>No destinations found for this style.</p>
    </div>
    <div id="styleViewAll" style="display:none; text-align:center; margin-top:28px;">
        <a href="/destinations" class="secondary-button" style="text-decoration:none; padding:10px 28px;">
            <i class="fas fa-compass"></i> View All Destinations
        </a>
    </div>
</div>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2 class="section-title">How It Works</h2>
    <div class="how-it-works-grid">
        <div class="how-step">
            <div class="how-step-number">1</div>
            <h3>Set Your Preferences</h3>
            <p>Choose your mood, travel dates, budget, and interests using our intuitive preference selector.</p>
        </div>
        <div class="how-step">
            <div class="how-step-number">2</div>
            <h3>Get AI Recommendations</h3>
            <p>Our algorithm analyzes thousands of destinations to suggest perfect matches for your trip.</p>
        </div>
        <div class="how-step">
            <div class="how-step-number">3</div>
            <h3>Build &amp; Book Itinerary</h3>
            <p>Customize your trip plan, add activities, and book flights directly through our system.</p>
        </div>
    </div>
</div>

<div class="ai-features-banner">
    <div style="text-align: center;">
        <h2>Powered by Advanced AI</h2>
        <p>Our intelligent algorithms analyze millions of data points to create your perfect trip</p>
    </div>
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number">10K+</div>
            <div>Destinations Analyzed</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">95%</div>
            <div>User Satisfaction Rate</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div>Real-Time Updates</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">50K+</div>
            <div>Happy Travelers</div>
        </div>
    </div>
</div>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2 class="section-title">Advanced Smart Features</h2>
    <p class="section-subtitle">Experience the future of travel planning with our AI-powered tools</p>
    <div class="smart-features-grid">
        <div class="smart-feature-card">
            <i class="fas fa-plane feature-icon"></i>
            <h3>Smart Flight Booking</h3>
            <p>AI-powered flight search finds the best deals, optimal routes, and perfect timing for your travels.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-robot feature-icon"></i>
            <h3>Predictive Weather Planning</h3>
            <p>AI predicts optimal travel dates based on historical weather patterns and seasonal trends.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-chart-pie feature-icon"></i>
            <h3>Real-Time Cost Analysis</h3>
            <p>Live price tracking for flights, hotels, and activities with alerts for price drops.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-heartbeat feature-icon"></i>
            <h3>Mood &amp; Interest Matching</h3>
            <p>Advanced personality profiling to match destinations with your emotional state.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-sync-alt feature-icon"></i>
            <h3>Dynamic Itinerary Adjuster</h3>
            <p>Automatically suggests itinerary changes based on real-time factors.</p>
        </div>
        <div class="smart-feature-card">
            <i class="fas fa-leaf feature-icon"></i>
            <h3>Sustainable Travel Options</h3>
            <p>Highlights eco-friendly accommodations and sustainable tourism activities.</p>
        </div>
    </div>
</div>

<section class="testimonials">
    <div class="testimonial-container">
        <h2 class="section-title">What Travelers Say</h2>
        <p class="section-subtitle">Join thousands of satisfied travelers who discovered their perfect trips</p>
        <div class="testimonial-grid" id="testimonialsGrid"></div>
    </div>
</section>

<div class="newsletter">
    <h2 class="section-title">Get Travel Inspiration</h2>
    <p class="section-subtitle">Subscribe to receive weekly destination ideas, travel tips, and exclusive deals</p>
    <div class="newsletter-input">
        <input type="email" id="newsletterEmail" placeholder="Enter your email address">
        <button onclick="subscribeNewsletter()">
            <i class="fas fa-paper-plane"></i> Subscribe
        </button>
    </div>
    <p class="privacy">We respect your privacy. Unsubscribe at any time.</p>
</div>
@endsection
