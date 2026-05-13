@extends('layouts.public')

@section('title', 'Privacy Policy — ' . config('app.name'))

@section('content')
<div class="static-page">
    <div class="static-hero">
        <h1>Privacy Policy</h1>
        <p>Last updated: {{ date('F j, Y') }}</p>
    </div>

    <div class="static-body">
        <section>
            <h2>Information We Collect</h2>
            <p>We collect information you provide when creating an account (name, email), planning trips, and making bookings. We also collect usage data to improve the service.</p>
        </section>

        <section>
            <h2>How We Use Your Information</h2>
            <ul>
                <li>To provide and personalise the Smart Booking service</li>
                <li>To process bookings and send confirmation emails</li>
                <li>To improve AI trip suggestions based on your preferences</li>
                <li>To communicate service updates and support responses</li>
            </ul>
        </section>

        <section>
            <h2>Third-Party Services</h2>
            <p>We integrate with the following third-party APIs to deliver our service:</p>
            <ul>
                <li><strong>Groq AI</strong> — AI-powered trip suggestions</li>
                <li><strong>AeroDataBox</strong> — Live flight data</li>
                <li><strong>Geoapify / OpenStreetMap</strong> — Accommodation and location data</li>
                <li><strong>Open Exchange Rates</strong> — Currency conversion</li>
            </ul>
        </section>

        <section>
            <h2>Data Security</h2>
            <p>We use industry-standard encryption and security practices to protect your data. Passwords are hashed and never stored in plain text.</p>
        </section>

        <section>
            <h2>Your Rights</h2>
            <p>You may request deletion of your account and associated data at any time from your profile settings. Contact us at <a href="{{ route('contact') }}">our contact page</a> for any privacy concerns.</p>
        </section>
    </div>
</div>
@endsection
