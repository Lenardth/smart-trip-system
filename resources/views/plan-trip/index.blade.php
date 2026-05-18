@extends('layouts.public')

@section('title', 'Plan Trip — Smart Booking')

@push('styles')
    
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@endpush

@section('content')
<section class="hero hero-with-image hero-pattern" 
         style="background-image: url('{{ $heroImage ?? 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1920&q=80' }}');">
    <div class="hero-content">
        <h1 class="hero-title">
            <i class="fas fa-route"></i> Plan Your Trip
        </h1>
        <p class="hero-subtitle">
            Let AI build the perfect itinerary tailored to your mood, budget, and style.
        </p>
    </div>
</section>

<div class="planner-wrap">
    <div class="steps">
        <div class="step active" id="si1"><div class="step-circle">1</div><div class="step-label">Mood & Style</div></div>
        <div class="step" id="si2"><div class="step-circle">2</div><div class="step-label">Trip Details</div></div>
        <div class="step" id="si3"><div class="step-circle">3</div><div class="step-label">Preferences</div></div>
        <div class="step" id="si4"><div class="step-circle">4</div><div class="step-label">Results</div></div>
    </div>

    <div class="planner-card" id="step1">
        <h3><i class="fas fa-heart"></i>How are you feeling?</h3>
        <p class="text-muted text-left mt-0">Choose a quick mood, pick one from the community, or describe your own feeling below.</p>

        <div class="mood-section-label"><i class="fas fa-th-large"></i> Quick picks</div>
        <div class="mood-grid">
            <div class="mood-card" data-mood="adventurous" data-action="selectMood"><div class="mood-icon"><i class="fas fa-hiking"></i></div><h4>Adventurous</h4><p>Thrills & exploration</p></div>
            <div class="mood-card" data-mood="relaxed" data-action="selectMood"><div class="mood-icon"><i class="fas fa-spa"></i></div><h4>Relaxed</h4><p>Peace & tranquility</p></div>
            <div class="mood-card" data-mood="cultural" data-action="selectMood"><div class="mood-icon"><i class="fas fa-landmark"></i></div><h4>Cultural</h4><p>History & art</p></div>
            <div class="mood-card" data-mood="romantic" data-action="selectMood"><div class="mood-icon"><i class="fas fa-heart"></i></div><h4>Romantic</h4><p>Love & escape</p></div>
            <div class="mood-card" data-mood="foodie" data-action="selectMood"><div class="mood-icon"><i class="fas fa-utensils"></i></div><h4>Foodie</h4><p>Cuisine & flavor</p></div>
            <div class="mood-card" data-mood="eco-travel" data-action="selectMood"><div class="mood-icon"><i class="fas fa-leaf"></i></div><h4>Eco-Travel</h4><p>Nature & sustainability</p></div>
        </div>

        <div class="community-moods">
            <div class="community-moods-title">
                <i class="fas fa-users"></i> From the community
                <span id="communityMoodCount" class="font-normal opacity-50"></span>
            </div>
            <div class="mood-pills" id="communityMoodPills">
                <div class="mood-pills-skeleton" id="communityMoodsLoading">
                    <span style="width:90px"></span>
                    <span style="width:110px"></span>
                    <span style="width:75px"></span>
                    <span style="width:130px"></span>
                    <span style="width:95px"></span>
                </div>
            </div>
        </div>

        <div class="custom-mood-box">
            <label for="customMoodInput">
                <i class="fas fa-pen-nib"></i> Add your own feeling
            </label>
            <div class="custom-mood-input-row">
                <input
                    type="text"
                    id="customMoodInput"
                    maxlength="80"
                    placeholder="e.g. burnt out and craving somewhere wild and disconnected…"
                    autocomplete="off"
                />
                <button class="btn-add-mood" id="btnAddMood" data-action="submitCustomMood">
                    <i class="fas fa-plus"></i> Add &amp; Use
                </button>
            </div>
            <p class="custom-mood-hint">
                <i class="fas fa-info-circle"></i>
                Your feeling will be saved and shown to future travellers as inspiration.
            </p>
            <div class="mood-submit-feedback" id="moodSubmitFeedback"></div>
        </div>

        <div class="selected-mood-display" id="selectedMoodDisplay">
            <i class="fas fa-check-circle"></i>
            Selected mood: <strong id="selectedMoodLabel"></strong>
        </div>
        <input type="hidden" id="selectedMoodValue" value="">

        <div class="form-group">
            <label for="feelingNote">Any extra context? <span class="opacity-50 font-normal">(optional)</span></label>
            <textarea id="feelingNote" rows="3" maxlength="500" placeholder="Example: I feel mentally tired and want calm beaches, nature walks, and peaceful local food spots."></textarea>
            <small class="d-block mt-8 text-muted">This helps AI personalise destination and accommodation style better.</small>
        </div>

        <div class="btn-row">
            <button class="secondary-button surprise-btn" data-action="surpriseMe">
                <i class="fas fa-dice"></i> Surprise Me!
            </button>
            <button class="primary-button" data-action="goStep" data-params='{"args":[2]}'>Next <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <div class="planner-card" id="step2" style="display:none">
        <h3><i class="fas fa-suitcase"></i>Trip Details</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Trip Duration</label>
                <div class="flex-gap-8-center">
                    <input type="number" id="durationDays" min="1" max="365" value="7"
                        class="form-input-number-sm"
                        data-input-action="syncDuration">
                    <span class="text-muted fs-13">days</span>
                    <input type="hidden" id="duration" value="7">
                </div>
                <small class="text-muted fs-11 mt-4 d-block">Enter any number of days (1–365)</small>
            </div>
            <div class="form-group">
                <label>Travel Companion</label>
                <select id="companion">
                    <option value="solo">Solo Traveller</option>
                    <option value="couple">Couple</option>
                    <option value="family_young">Family with Young Children</option>
                    <option value="family_teens">Family with Teenagers</option>
                    <option value="friends_small">Small Group of Friends (2–4)</option>
                    <option value="friends_large">Large Group of Friends (5+)</option>
                    <option value="business">Business Traveller</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Budget Range</label>
                <select id="budget">
                    <option value="backpacker">Backpacker — under 500 USD</option>
                    <option value="budget">Budget-Friendly — 500–1,500 USD</option>
                    <option value="mid" selected>Mid-Range — 1,500–4,000 USD</option>
                    <option value="premium">Premium — 4,000–8,000 USD</option>
                    <option value="luxury">Luxury — 8,000+ USD</option>
                </select>
            </div>
            <div class="form-group">
                <label>Departure Month</label>
                <select id="month">
                    <option value="">— Any time —</option>
                    <option>January</option><option>February</option><option>March</option>
                    <option>April</option><option>May</option><option>June</option>
                    <option>July</option><option>August</option><option>September</option>
                    <option>October</option><option>November</option><option>December</option>
                </select>
            </div>
        </div>
        <div class="btn-row">
            <button class="secondary-button" data-action="goStep" data-params='{"args":[1]}'><i class="fas fa-arrow-left"></i> Back</button>
            <button class="primary-button" data-action="goStep" data-params='{"args":[3]}'>Next <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <div class="planner-card" id="step3" style="display:none">
        <h3><i class="fas fa-sliders-h"></i>Preferences</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Preferred Region</label>
                <select id="region">
                    <option value="any">— No preference —</option>
                    <option value="europe">Europe</option>
                    <option value="southeast_asia">Southeast Asia</option>
                    <option value="east_asia">East Asia</option>
                    <option value="south_asia">South Asia</option>
                    <option value="middle_east">Middle East</option>
                    <option value="africa">Africa</option>
                    <option value="north_america">North America</option>
                    <option value="latin_america">Latin America</option>
                    <option value="oceania">Oceania</option>
                    <option value="caribbean">Caribbean</option>
                </select>
            </div>
            <div class="form-group">
                <label>Accommodation Style</label>
                <select id="accommodation">
                    <option value="any">— No preference —</option>
                    <option value="hostel">Hostel / Dorm</option>
                    <option value="budget_hotel">Budget Hotel</option>
                    <option value="boutique">Boutique Hotel</option>
                    <option value="resort">Resort</option>
                    <option value="villa">Private Villa</option>
                    <option value="airbnb">Apartment / Airbnb</option>
                    <option value="glamping">Glamping / Eco-Lodge</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Flying From (City or Airport)</label>
                <input type="text" id="origin" placeholder="e.g. London, Dubai, New York">
            </div>
            <div class="form-group">
                <label>Experience Level</label>
                <select id="experience">
                    <option value="">— Not specified —</option>
                    <option value="first_time">First-time traveller</option>
                    <option value="occasional">Occasional traveller</option>
                    <option value="experienced">Experienced traveller</option>
                    <option value="frequent">Frequent / seasoned traveller</option>
                </select>
            </div>
        </div>
        <div class="btn-row">
            <button class="secondary-button" data-action="goStep" data-params='{"args":[2]}'><i class="fas fa-arrow-left"></i> Back</button>
            <button class="primary-button" data-action="generateSuggestions">
                <i class="fas fa-magic"></i> Find My Destinations
            </button>
        </div>
    </div>

    <div id="step4" class="hidden" style="display:none">
        <div id="loadingState" class="loading-state">
            <div class="spinner"></div>
            <p id="loadingMsg">Finding your perfect destinations…</p>
            <div class="loading-fun-fact" id="loadingFunFact"></div>
        </div>
        <div id="errorState" class="error-state"></div>
        <div id="resultsState" class="results-section">
            <h2 class="section-title">Choose Your Destination</h2>
            <p class="section-subtitle">5 destinations matched to your preferences. Click one to select it, then print your trip receipt.</p>
            <p class="select-hint"><i class="fas fa-hand-pointer"></i>Tap a card to select your destination</p>
            <div class="results-grid" id="resultsGrid"></div>
            <div id="travelAdvisoryContainer" class="hidden"></div>
        </div>
        <div class="btn-row">
            <button class="secondary-button" data-action="goStep" data-params='{"args":[3]}'><i class="fas fa-arrow-left"></i> Adjust Preferences</button>
            <button class="primary-button" data-action="generateSuggestions"><i class="fas fa-sync-alt"></i> Regenerate</button>
            <button class="pdf-button" id="receiptBtn" data-action="openReceipt"><i class="fas fa-receipt"></i> View &amp; Print Receipt</button>
            <button class="primary-button" id="saveBtn"><i class="fas fa-bookmark"></i> Save to Dashboard</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="receiptModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-receipt"></i>Trip Receipt</h2>
            <button class="modal-close" data-action="closeReceipt">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="receipt" id="receiptContent"></div>
            <div class="btn-row">
                <button class="secondary-button" data-action="closeReceipt"><i class="fas fa-times"></i> Close</button>
                <button class="primary-button" data-action="printReceipt"><i class="fas fa-print"></i> Print</button>
                <button class="pdf-button" data-action="downloadReceiptPdf"><i class="fas fa-file-pdf"></i> Save PDF</button>
            </div>
        </div>
    </div>
</div>
@endsection