@extends('layouts.public')

@section('title', 'Terms of Service — ' . config('app.name'))

@section('content')
<div class="static-page">
    <div class="static-hero">
        <h1>Terms of Service</h1>
        <p>Last updated: {{ date('F j, Y') }}</p>
    </div>

    <div class="static-body">
        <section>
            <h2>Acceptance of Terms</h2>
            <p>By using Smart Booking, you agree to these terms. If you do not agree, please do not use the service.</p>
        </section>

        <section>
            <h2>Use of the Service</h2>
            <ul>
                <li>You must be at least 18 years old to create an account</li>
                <li>You are responsible for maintaining the security of your account</li>
                <li>You may not use the service for any unlawful purpose</li>
                <li>AI-generated trip suggestions are for informational purposes only</li>
            </ul>
        </section>

        <section>
            <h2>Bookings and Payments</h2>
            <p>Smart Booking facilitates booking management. Flight and accommodation prices are sourced from third-party APIs and may vary. We are not responsible for price changes after a booking is confirmed.</p>
        </section>

        <section>
            <h2>Cancellations</h2>
            <p>Bookings may be cancelled from your bookings page. Refund policies depend on the specific flight or accommodation provider.</p>
        </section>

        <section>
            <h2>Limitation of Liability</h2>
            <p>Smart Booking is provided "as is". We are not liable for any indirect, incidental, or consequential damages arising from use of the service.</p>
        </section>

        <section>
            <h2>Changes to Terms</h2>
            <p>We may update these terms at any time. Continued use of the service after changes constitutes acceptance of the new terms.</p>
        </section>

        <section>
            <h2>Contact</h2>
            <p>Questions about these terms? <a href="{{ route('contact') }}">Contact us</a>.</p>
        </section>
    </div>
</div>
@endsection
