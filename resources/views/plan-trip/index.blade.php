@extends('layouts.public')

@section('title', 'Plan Trip — Smart Booking')

@push('styles')
    <style>
        .mood-section-label {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .community-moods { margin-bottom: 20px; }
        .community-moods-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .community-moods-title i { color: var(--gold); }
        .mood-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            min-height: 36px;
        }
        .mood-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1.5px solid var(--gold, #c9a96e);
            background: transparent;
            color: var(--deep, #3b1f2b);
            font-size: 13px;
            cursor: pointer;
            transition: background .18s, color .18s, transform .12s;
            user-select: none;
        }
        .mood-pill:hover { background: rgba(201,169,110,.15); transform: translateY(-1px); }
        .mood-pill.selected { background: var(--gold, #c9a96e); color: var(--deep, #3b1f2b); font-weight: 700; }
        .mood-pill .pill-icon { font-size: 13px; color: inherit; }
        .mood-pill .pill-count { font-size: 11px; opacity: .6; font-weight: 400; }
        .mood-pills-skeleton { display: flex; gap: 8px; flex-wrap: wrap; }
        .mood-pills-skeleton span {
            display: inline-block;
            height: 32px;
            border-radius: 999px;
            background: rgba(0,0,0,.07);
            animation: shimmer 1.4s infinite;
        }
        @keyframes shimmer { 0%,100%{opacity:.4} 50%{opacity:.9} }

        .custom-mood-box {
            background: rgba(201,169,110,.06);
            border: 1.5px solid rgba(201,169,110,.30);
            border-radius: 10px;
            padding: 18px 20px;
            margin-top: 20px;
        }
        .custom-mood-box > label {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }
        .custom-mood-box > label i { color: var(--gold); }
        .custom-mood-input-row { display: flex; gap: 10px; align-items: flex-start; }

        .custom-mood-input-row input[type="text"] {
            flex: 1;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid rgba(201,169,110,.45);
            background: #fff;
            color: var(--deep, #3b1f2b);
            font-size: 15px;
            font-weight: 400;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            caret-color: var(--gold, #c9a96e);
        }
        .custom-mood-input-row input[type="text"]:focus {
            border-color: var(--gold, #c9a96e);
            box-shadow: 0 0 0 3px rgba(201,169,110,.15);
        }
        .custom-mood-input-row input[type="text"]::placeholder {
            color: rgba(59,31,43,.38);
            font-style: italic;
        }

        .btn-add-mood {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            background: var(--gold, #c9a96e);
            color: var(--deep, #3b1f2b);
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            transition: opacity .2s, transform .12s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-add-mood:hover { opacity: .85; transform: translateY(-1px); }
        .btn-add-mood:disabled { opacity: .45; cursor: not-allowed; transform: none; }
        .custom-mood-hint { font-size: 11.5px; color: var(--text-muted); margin-top: 8px; }
        .custom-mood-hint i { margin-right: 4px; }
        .mood-submit-feedback {
            font-size: 13px;
            margin-top: 8px;
            min-height: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .mood-submit-feedback.success { color: #3a7d44; }
        .mood-submit-feedback.error   { color: #c0392b; }

        .selected-mood-display {
            margin-top: 14px;
            padding: 10px 14px;
            border-radius: 8px;
            background: rgba(201,169,110,.12);
            border: 1px solid rgba(201,169,110,.35);
            font-size: 13.5px;
            color: var(--deep, #3b1f2b);
            display: none;
            align-items: center;
            gap: 8px;
        }
        .selected-mood-display.visible { display: flex; }
        .selected-mood-display i { color: var(--gold); }
        .selected-mood-display strong { color: var(--gold); }

        /* Surprise summary panel */
        .surprise-summary {
            display: none;
            margin-top: 20px;
            background: var(--gold-dim);
            border: 1.5px solid var(--gold);
            border-radius: 10px;
            padding: 18px 20px;
            animation: fadeSlideIn .3s ease;
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .surprise-summary-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--deep);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .surprise-summary-title i { color: var(--gold); }
        .surprise-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .surprise-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
        }
        .surprise-item i {
            color: var(--gold);
            font-size: 13px;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .surprise-item span {
            font-size: 12.5px;
            color: var(--deep);
            line-height: 1.4;
        }
        .surprise-item strong {
            display: block;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 2px;
        }
        .surprise-summary-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .surprise-summary-actions .primary-button,
        .surprise-summary-actions .secondary-button {
            font-size: 13px;
            padding: 10px 20px;
        }

        /* Surprise Me button */
        .surprise-btn {
            background: transparent;
            border: 1.5px dashed var(--gold);
            color: var(--deep);
            font-size: 13px;
            transition: background .2s, transform .15s;
        }
        .surprise-btn:hover {
            background: var(--gold-dim);
            transform: rotate(-2deg) scale(1.04);
        }

        /* Surprise toast */
        .surprise-toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: var(--deep);
            color: var(--gold);
            padding: 12px 24px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Georgia', serif;
            box-shadow: 0 8px 24px rgba(0,0,0,.3);
            z-index: 9999;
            opacity: 0;
            transition: transform .35s cubic-bezier(.34,1.56,.64,1), opacity .35s;
            pointer-events: none;
            white-space: nowrap;
        }
        .surprise-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* Animated loading */
        #loadingMsg {
            transition: opacity .2s;
        }
        .loading-fun-fact {
            margin-top: 16px;
            padding: 12px 18px;
            background: var(--gold-dim);
            border: 1px solid rgba(201,169,110,.3);
            border-radius: 8px;
            font-size: 13px;
            color: var(--deep);
            max-width: 420px;
            text-align: center;
            line-height: 1.5;
            transition: opacity .4s;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@endpush

@section('content')
<section class="page-hero" style="background: linear-gradient(160deg, rgba(5,25,15,0.75) 0%, rgba(59,31,43,0.50) 100%), url('https://images.unsplash.com/photo-1501555088652-021faa106b9b?w=1920&q=90'); background-size: cover; background-position: center;">
    <div>
        <h1><i class="fas fa-route"></i> Plan Your Trip</h1>
        <p id="heroTagline">Let AI build the perfect itinerary tailored to your mood, budget, and style.</p>
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
        <h3><i class="fas fa-heart" style="color:var(--gold);margin-right:8px;"></i>How are you feeling?</h3>
        <p style="color:var(--text-muted);text-align:left;margin-top:0;">Choose a quick mood, pick one from the community, or describe your own feeling below.</p>

        <div class="mood-section-label"><i class="fas fa-th-large"></i> Quick picks</div>
        <div class="mood-grid">
            <div class="mood-card" data-mood="adventurous" onclick="selectMood(this)"><div class="mood-icon"><i class="fas fa-hiking"></i></div><h4>Adventurous</h4><p>Thrills & exploration</p></div>
            <div class="mood-card" data-mood="relaxed" onclick="selectMood(this)"><div class="mood-icon"><i class="fas fa-spa"></i></div><h4>Relaxed</h4><p>Peace & tranquility</p></div>
            <div class="mood-card" data-mood="cultural" onclick="selectMood(this)"><div class="mood-icon"><i class="fas fa-landmark"></i></div><h4>Cultural</h4><p>History & art</p></div>
            <div class="mood-card" data-mood="romantic" onclick="selectMood(this)"><div class="mood-icon"><i class="fas fa-heart"></i></div><h4>Romantic</h4><p>Love & escape</p></div>
            <div class="mood-card" data-mood="foodie" onclick="selectMood(this)"><div class="mood-icon"><i class="fas fa-utensils"></i></div><h4>Foodie</h4><p>Cuisine & flavor</p></div>
            <div class="mood-card" data-mood="eco-travel" onclick="selectMood(this)"><div class="mood-icon"><i class="fas fa-leaf"></i></div><h4>Eco-Travel</h4><p>Nature & sustainability</p></div>
        </div>

        <div class="community-moods" style="margin-top:24px;">
            <div class="community-moods-title">
                <i class="fas fa-users"></i> From the community
                <span id="communityMoodCount" style="font-weight:400;opacity:.5;"></span>
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
                <button class="btn-add-mood" id="btnAddMood" onclick="submitCustomMood()">
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

        <div class="form-group" style="margin-top:20px;">
            <label for="feelingNote">Any extra context? <span style="opacity:.5;font-weight:400;">(optional)</span></label>
            <textarea id="feelingNote" rows="3" maxlength="500" placeholder="Example: I feel mentally tired and want calm beaches, nature walks, and peaceful local food spots."></textarea>
            <small style="display:block;margin-top:8px;color:var(--text-muted);">This helps AI personalise destination and accommodation style better.</small>
        </div>

        <div class="btn-row">
            <button class="secondary-button surprise-btn" onclick="surpriseMe()">
                <i class="fas fa-dice"></i> Surprise Me!
            </button>
            <button class="primary-button" onclick="goStep(2)">Next <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <div class="planner-card" id="step2" style="display:none;">
        <h3><i class="fas fa-suitcase" style="color:var(--gold);margin-right:8px;"></i>Trip Details</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Trip Duration</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="number" id="durationDays" min="1" max="365" value="7"
                        style="width:80px;padding:11px 10px;border:1.5px solid var(--border);border-radius:6px;font-size:14px;font-family:'Georgia',serif;background:#fff;color:var(--deep);"
                        oninput="document.getElementById('duration').value=this.value">
                    <span style="color:var(--text-muted);font-size:13px;">days</span>
                    <input type="hidden" id="duration" value="7">
                </div>
                <small style="color:var(--text-muted);font-size:11.5px;margin-top:4px;display:block;">Enter any number of days (1–365)</small>
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
                    <option value="backpacker">Backpacker (under $500)</option>
                    <option value="budget">Budget-Friendly ($500–$1,500)</option>
                    <option value="mid" selected>Mid-Range ($1,500–$4,000)</option>
                    <option value="premium">Premium ($4,000–$8,000)</option>
                    <option value="luxury">Luxury ($8,000+)</option>
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
            <button class="secondary-button" onclick="goStep(1)"><i class="fas fa-arrow-left"></i> Back</button>
            <button class="primary-button" onclick="goStep(3)">Next <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <div class="planner-card" id="step3" style="display:none;">
        <h3><i class="fas fa-sliders-h" style="color:var(--gold);margin-right:8px;"></i>Preferences</h3>
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
            <button class="secondary-button" onclick="goStep(2)"><i class="fas fa-arrow-left"></i> Back</button>
            <button class="primary-button" onclick="generateSuggestions()" style="font-size:17px;padding:14px 40px;">
                <i class="fas fa-magic"></i> Find My Destinations
            </button>
        </div>
    </div>

    <div id="step4" style="display:none;">
        <div id="loadingState" class="loading-state">
            <div class="spinner"></div>
            <p id="loadingMsg">Finding your perfect destinations…</p>
            <div class="loading-fun-fact" id="loadingFunFact"></div>
        </div>
        <div id="errorState" class="error-state" style="display:none;"></div>
        <div id="resultsState" class="results-section" style="display:none;">
            <h2 class="section-title">Choose Your Destination</h2>
            <p class="section-subtitle">5 destinations matched to your preferences. Click one to select it, then print your trip receipt.</p>
            <p class="select-hint"><i class="fas fa-hand-pointer" style="margin-right:6px;"></i>Tap a card to select your destination</p>
            <div class="results-grid" id="resultsGrid"></div>
            <div id="travelAdvisoryContainer" style="display:none;"></div>
        </div>
        <div class="btn-row" style="margin-top:30px;">
            <button class="secondary-button" onclick="goStep(3)"><i class="fas fa-arrow-left"></i> Adjust Preferences</button>
            <button class="primary-button" onclick="generateSuggestions()"><i class="fas fa-sync-alt"></i> Regenerate</button>
            <button class="pdf-button" id="receiptBtn" onclick="openReceipt()" style="display:none;"><i class="fas fa-receipt"></i> View &amp; Print Receipt</button>
            <button class="primary-button" id="saveBtn" style="display:none;background:var(--deep);color:var(--text-light);"><i class="fas fa-bookmark"></i> Save to Dashboard</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="receiptModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-receipt" style="color:var(--gold);margin-right:10px;"></i>Trip Receipt</h2>
            <button class="modal-close" onclick="closeReceipt()">&#x2715;</button>
        </div>
        <div class="modal-body">
            <div class="receipt" id="receiptContent"></div>
            <div class="btn-row" style="margin-top:20px;">
                <button class="secondary-button" onclick="closeReceipt()"><i class="fas fa-times"></i> Close</button>
                <button class="primary-button" onclick="printReceipt()"><i class="fas fa-print"></i> Print</button>
                <button class="pdf-button" onclick="downloadReceiptPdf()"><i class="fas fa-file-pdf"></i> Save PDF</button>
            </div>
        </div>
    </div>
</div>
@endsection