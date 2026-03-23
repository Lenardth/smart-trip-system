<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accommodations — Smart Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([
        'resources/css/blade/base.css',
        'resources/css/blade/accommodations/index.css',
        'resources/js/blade/base.js',
        'resources/js/blade/accommodations/index.js'
    ])
</head>
<body>
@include('partials.public-navigation')

<main class="accommodations-wrap">
    <section class="head">
        <h1>Find Accommodation</h1>
        <p>Browse accommodation options and match your trip style.</p>
    </section>

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
        <button id="reloadBtn" type="button">Search</button>
    </section>

    <section id="accommodationsGrid" class="grid"></section>
    <div id="emptyState" class="empty" style="display:none;">No accommodations found.</div>
</main>

@include('partials.public-footer')
</body>
</html>=
