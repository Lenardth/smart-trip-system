{{-- resources/views/plan-trip/index.blade.php --}}
@extends('layouts.public')

@section('title', 'Plan Trip — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/plan-trip/index.css'])
@endpush

@push('scripts')
    @vite(['resources/js/blade/plan-trip/index.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@endpush

@section('content')
<section class="page-hero">
    <div>
        <h1><i class="fas fa-route"></i> Plan Your Trip</h1>
        <p>Let AI build the perfect itinerary tailored to your mood, budget, and style.</p>
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
        <p style="color:var(--text-muted);text-align:left;margin-top:0;">Choose the mood that best describes what kind of trip you're looking for.</p>
        <div class="mood-grid">
            <div class="mood-card" data-mood="adventurous"><div class="mood-icon"><i class="fas fa-hiking"></i></div><h4>Adventurous</h4><p>Thrills & exploration</p></div>
            <div class="mood-card" data-mood="relaxed"><div class="mood-icon"><i class="fas fa-spa"></i></div><h4>Relaxed</h4><p>Peace & tranquility</p></div>
            <div class="mood-card" data-mood="cultural"><div class="mood-icon"><i class="fas fa-landmark"></i></div><h4>Cultural</h4><p>History & art</p></div>
            <div class="mood-card" data-mood="romantic"><div class="mood-icon"><i class="fas fa-heart"></i></div><h4>Romantic</h4><p>Love & escape</p></div>
            <div class="mood-card" data-mood="foodie"><div class="mood-icon"><i class="fas fa-utensils"></i></div><h4>Foodie</h4><p>Cuisine & flavor</p></div>
            <div class="mood-card" data-mood="eco-travel"><div class="mood-icon"><i class="fas fa-leaf"></i></div><h4>Eco-Travel</h4><p>Nature & sustainability</p></div>
        </div>
        <div class="form-group">
            <label for="feelingNote">Describe how you feel (optional)</label>
            <textarea id="feelingNote" rows="4" maxlength="500" placeholder="Example: I feel mentally tired and want calm beaches, nature walks, and peaceful local food spots."></textarea>
            <small style="display:block;margin-top:8px;color:var(--text-muted);">This helps AI personalize destination and accommodation style better.</small>
        </div>
        <div class="btn-row"><button class="primary-button" onclick="goStep(2)">Next <i class="fas fa-arrow-right"></i></button></div>
    </div>

    <div class="planner-card" id="step2" style="display:none;">
        <h3><i class="fas fa-suitcase" style="color:var(--gold);margin-right:8px;"></i>Trip Details</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Trip Duration</label>
                <select id="duration">
                    <option value="weekend">Long Weekend (3–4 days)</option>
                    <option value="week" selected>One Week (7 days)</option>
                    <option value="two_weeks">Two Weeks (10–14 days)</option>
                    <option value="month">One Month or more</option>
                    <option value="flexible">Flexible / Open-ended</option>
                </select>
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
            <p>Finding your perfect destinations…</p>
        </div>
        <div id="errorState" class="error-state" style="display:none;"></div>
        <div id="resultsState" class="results-section" style="display:none;">
            <h2 class="section-title">Choose Your Destination</h2>
            <p class="section-subtitle">5 destinations matched to your preferences. Click one to select it, then print your trip receipt.</p>
            <p class="select-hint"><i class="fas fa-hand-pointer" style="margin-right:6px;"></i>Tap a card to select your destination</p>
            <div class="results-grid" id="resultsGrid"></div>
        </div>
        <div class="btn-row" style="margin-top:30px;">
            <button class="secondary-button" onclick="goStep(3)"><i class="fas fa-arrow-left"></i> Adjust Preferences</button>
            <button class="primary-button" onclick="generateSuggestions()"><i class="fas fa-sync-alt"></i> Regenerate</button>
            <button class="pdf-button" id="receiptBtn" onclick="openReceipt()" style="display:none;"><i class="fas fa-receipt"></i> View & Print Receipt</button>
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
