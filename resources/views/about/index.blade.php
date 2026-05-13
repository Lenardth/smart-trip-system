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
            <h2>Our Core Features</h2>
            <ul>
                <li><strong>AI Trip Planner</strong> — Tell us your mood, budget, and travel style. Our AI suggests personalised destinations with real cost estimates.</li>
                <li><strong>Flight Search</strong> — Search live flight data across hundreds of routes with real-time pricing.</li>
                <li><strong>Accommodation Search</strong> — Find hotels, hostels, resorts, and apartments powered by live API data.</li>
                <li><strong>Booking Management</strong> — Book flights and stays, apply coupons, and track all your bookings in one dashboard.</li>
                <li><strong>Currency Converter</strong> — See prices in your preferred currency with live exchange rates.</li>
                <li><strong>Dashboard</strong> — A personalised overview of your trips, bookings, and recent activity.</li>
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
