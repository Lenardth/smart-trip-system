@extends('layouts.public')

@section('title', 'About Us — ' . config('app.name'))

@section('content')
<div class="static-page">
    <div class="static-hero">
        <h1>About Smart Booking</h1>
        <p>AI-powered travel planning for modern explorers.</p>
    </div>

    <div class="static-body">
        <section>
            <h2>What We Do</h2>
            <p>Smart Booking combines artificial intelligence with real-time travel data to help you plan trips, find flights, discover accommodations, and manage bookings — all in one place.</p>
        </section>

        <section>
            <h2>Core Features</h2>
            <ul class="feature-list">
                <li><strong>AI Trip Planning</strong> — Mood, budget, and companion scoring with Groq-powered day-by-day ideas.</li>
                <li><strong>Flights &amp; Bookings</strong> — Search live fares, book seats, and manage everything in one place.</li>
                <li><strong>Itineraries &amp; PDFs</strong> — Build editable plans and export polished PDFs and receipts.</li>
                <li><strong>Destinations &amp; Discovery</strong> — Explore places, save a wishlist, and compare destinations side by side.</li>
                <li><strong>Multi-Currency &amp; Pricing</strong> — Live exchange rates and receipts in the currency you choose.</li>
            </ul>
        </section>

        <section>
            <h2>Built With</h2>
            <p>Smart Booking is built on Laravel, powered by the Groq AI API (Llama 3.3), and integrates with AeroDataBox for flights, Geoapify for accommodations, and Open Exchange Rates for currency conversion.</p>
        </section>

        <section>
            <h2>Contact Us</h2>
            <p>Have questions or feedback? <a href="{{ route('contact') }}">Get in touch</a>.</p>
        </section>
    </div>
</div>
@endsection
