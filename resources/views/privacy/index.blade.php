@extends('layouts.public')

@section('title', 'Privacy Policy — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/base.css'])
    <style>
        .static-hero {
            padding: 5rem 2rem 3.5rem;
            background: linear-gradient(160deg, rgba(80,40,10,0.72) 0%, rgba(20,10,5,0.55) 100%),
                        url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1800&q=80') center/cover no-repeat;
            text-align: center;
            color: #fff;
        }
        .static-hero h1 { font-size: 2.4rem; margin: 0 0 .6rem; letter-spacing: .03em; }
        .static-hero p  { font-size: 1.05rem; opacity: .85; max-width: 540px; margin: 0 auto; }

        .static-body {
            max-width: 780px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 5rem;
            color: var(--text-sub, #333);
            line-height: 1.8;
        }

        .legal-meta {
            font-size: .82rem;
            color: var(--text-muted, #888);
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border, #e8dcc8);
        }

        .legal-section {
            margin-bottom: 2.25rem;
        }

        .legal-section h2 {
            font-size: 1.08rem;
            font-weight: 700;
            color: var(--deep, #1a0a00);
            border-left: 3px solid var(--gold, #b8860b);
            padding-left: .75rem;
            margin: 0 0 .85rem;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .legal-section p {
            margin: 0 0 .85rem;
            font-size: .94rem;
        }

        .legal-section ul {
            margin: 0 0 .85rem 1.25rem;
            padding: 0;
            font-size: .94rem;
        }

        .legal-section ul li {
            margin-bottom: .4rem;
        }

        .legal-section ul li::marker {
            color: var(--gold, #b8860b);
        }

        .legal-divider {
            border: none;
            border-top: 1px solid var(--border, #e8dcc8);
            margin: 2rem 0;
        }

        .legal-contact-box {
            background: var(--card-bg, #fffdf7);
            border: 1px solid var(--border, #e8dcc8);
            border-left: 4px solid var(--gold, #b8860b);
            border-radius: 6px;
            padding: 1.25rem 1.5rem;
            font-size: .9rem;
            color: var(--text-sub, #333);
        }

        .legal-contact-box a {
            color: var(--gold, #b8860b);
            text-decoration: none;
        }

        .legal-contact-box a:hover { text-decoration: underline; }
    </style>
@endpush

@section('content')

<section class="static-hero">
    <h1><i class="fas fa-shield-halved"></i> Privacy Policy</h1>
    <p>How we collect, use, and protect your personal information.</p>
</section>

<div class="static-body">

    <p class="legal-meta">
        <i class="fas fa-calendar-alt"></i> Last updated: {{ date('F j, Y') }} &nbsp;·&nbsp;
        <i class="fas fa-globe"></i> Applies to smart-trip-system.vercel.app
    </p>

    <div class="legal-section">
        <h2>1. Introduction</h2>
        <p>Smart Booking ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform. Please read this policy carefully. If you disagree with its terms, please discontinue use of the service.</p>
    </div>

    <div class="legal-section">
        <h2>2. Information We Collect</h2>
        <p>We may collect the following categories of information:</p>
        <ul>
            <li><strong>Account Data:</strong> Name, email address, password (hashed), and profile picture when you register.</li>
            <li><strong>Booking Data:</strong> Trip details, accommodation preferences, travel dates, and payment references.</li>
            <li><strong>Usage Data:</strong> Pages visited, features used, search queries, and interaction timestamps.</li>
            <li><strong>Technical Data:</strong> IP address, browser type, device identifiers, and last login metadata.</li>
            <li><strong>Communications:</strong> Messages sent through our real-time chat system.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>3. How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul>
            <li>Provide, operate, and improve the Smart Booking platform.</li>
            <li>Process and manage your accommodation bookings and trip plans.</li>
            <li>Send transactional notifications related to your account and bookings.</li>
            <li>Personalise AI-powered recommendations based on your preferences.</li>
            <li>Ensure platform security and detect fraudulent activity.</li>
            <li>Comply with legal obligations.</li>
        </ul>
        <p>We do not sell your personal information to third parties.</p>
    </div>

    <div class="legal-section">
        <h2>4. Cookies & Tracking</h2>
        <p>We use session cookies and local storage to maintain your authenticated session and preferences. We do not use third-party advertising trackers. You may disable cookies in your browser settings, though some features may not function correctly without them.</p>
    </div>

    <div class="legal-section">
        <h2>5. Third-Party Services</h2>
        <p>Our platform integrates with the following third-party services, each governed by their own privacy policies:</p>
        <ul>
            <li><strong>Pusher</strong> — real-time messaging infrastructure.</li>
            <li><strong>Neon Postgres</strong> — cloud database hosting.</li>
            <li><strong>GNews API</strong> — local destination news headlines.</li>
            <li><strong>OpenStreetMap / Nominatim</strong> — map tiles and geocoding.</li>
            <li><strong>Vercel</strong> — application hosting and edge delivery.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>6. Data Retention</h2>
        <p>We retain your account and booking data for as long as your account remains active, or as required by law. You may request deletion of your account and associated data at any time by contacting us.</p>
    </div>

    <div class="legal-section">
        <h2>7. Your Rights</h2>
        <p>Depending on your jurisdiction, you may have the right to:</p>
        <ul>
            <li>Access the personal data we hold about you.</li>
            <li>Request correction of inaccurate data.</li>
            <li>Request erasure of your personal data.</li>
            <li>Object to or restrict certain processing.</li>
            <li>Data portability where applicable.</li>
        </ul>
        <p>To exercise any of these rights, contact us using the details below.</p>
    </div>

    <div class="legal-section">
        <h2>8. Security</h2>
        <p>We implement industry-standard security measures including hashed passwords (bcrypt), HTTPS-only transport, CSRF protection, and role-based access controls. However, no method of transmission over the internet is 100% secure.</p>
    </div>

    <div class="legal-section">
        <h2>9. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by updating the "Last updated" date above. Continued use of the platform after changes constitutes acceptance of the revised policy.</p>
    </div>

    <hr class="legal-divider">

    <div class="legal-contact-box">
        <i class="fas fa-envelope"></i> <strong>Questions about this policy?</strong><br>
        Contact us at <a href="mailto:support@smartbooking.app">support@smartbooking.app</a> or visit our <a href="{{ route('contact') }}">Contact page</a>.
    </div>

</div>

@endsection
