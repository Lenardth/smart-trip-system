@extends('layouts.public')

@section('title', 'Accommodations — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/accommodations/index.css'])
@endpush

@push('scripts_body')
    @vite(['resources/js/blade/accommodations/index.js'])
@endpush

@section('content')
<section class="page-hero accommodations-hero" style="background: linear-gradient(rgba(30, 15, 20, .6), rgba(30, 15, 20, .6)), url('https://images.pexels.com/photos/414171/pexels-photo-414171.jpeg'); background-size: cover; background-position: center;">
    <div>
        <h1><i class="fas fa-hotel"></i> Find Accommodation</h1>
        <p>Browse accommodation options and match your trip style.</p>
    </div>
</section>

<main class="accommodations-wrap">
    <section class="filters">
        <input id="searchInput" type="text" placeholder="Search by city, country, or name">
        <select id="styleSelect">
            <option value="any">Any style</option>
            <option value="hostel">Hostel</option>
            <option value="budget_hotel">Budget Hotel</option>
            <option value="boutique">Boutique</option>
            <option value="resort">Resort</option>
            <option value="villa">Villa</option>
            <option value="airbnb">Airbnb</option>
            <option value="glamping">Glamping</option>
        </select>
        <select id="budgetSelect">
            <option value="any">Any budget</option>
            <option value="backpacker">Backpacker</option>
            <option value="budget">Budget</option>
            <option value="mid">Mid-range</option>
            <option value="premium">Premium</option>
            <option value="luxury">Luxury</option>
        </select>
        <button id="reloadBtn" type="button"><i class="fas fa-search"></i> Search</button>
    </section>

    <section id="aiMatchPanel" class="ai-match" style="display:none;">
        <h3><i class="fas fa-wand-magic-sparkles"></i> AI Accommodation Match</h3>
        <p id="aiMatchSummary"></p>
    </section>

    <section id="accommodationsGrid" class="grid"></section>
    <div id="emptyState" class="empty" style="display:none;">No accommodations found.</div>
</main>
@endsection
