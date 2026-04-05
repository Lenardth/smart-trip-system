@extends('layouts.public')

@section('title', 'Terms of Service — Smart Booking')

@push('styles')
    <style>
        .static-hero {
            padding: 5rem 2rem 3.5rem;
            background: linear-gradient(160deg, rgba(80,40,10,0.72) 0%, rgba(20,10,5,0.55) 100%),
                        url('https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=1800&q=80') center/cover no-repeat;
            text-align: center;
            color: #fff;
        }
        .static-hero h1 { font-size: 2.4rem; margin: 0 0 .6rem; letter-spacing: .03em; }
        .static-hero p  { font-size: 1.05rem; opacity: .85; max-width: 540px; margin: 0 auto; }

        .static-body {
            max-width: 780px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 5rem;
            color: #2c2c2c;
            line-height: 1.8;
        }

        .legal-meta {
            font-size: .82rem;
            color: #6b5b4f;
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
            color: var(--deep, #3b1f2b);
            border-left: 3px solid var(--gold, #c9a96e);
            padding-left: .75rem;
            margin: 0 0 .85rem;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .legal-section p {
            margin: 0 0 .85rem;
            font-size: .94rem;
            color: #2c2c2c;
        }

        .legal-section ul {
            margin: 0 0 .85rem 1.25rem;
            padding: 0;
            font-size: .94rem;
            color: #2c2c2c;
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
            border-left: 4px solid var(--gold, #c9a96e);
            border-radius: 6px;
            padding: 1.25rem 1.5rem;
            font-size: .9rem;
            color: #2c2c2c;
        }

        .legal-contact-box a {
            color: var(--gold, #b8860b);
            text-decoration: none;
        }

        .legal-contact-box a:hover { text-decoration: underline; }

        .terms-highlight {
            background: #fffbf0;
            border: 1px solid #e8dcc8;
            border-radius: 6px;
            padding: 1rem 1.25rem;
            font-size: .88rem;
            color: var(--deep, #1a0a00);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .terms-highlight i { color: var(--gold, #b8860b); margin-right: .4rem; }
    </style>
@endpush

@section('content')

<section class="static-hero">
    <h1><i class="fas fa-file-contract"></i> Terms of Service</h1>
    <p>The rules and agreements governing your use of Smart Booking.</p>
</section>

<div class="static-body">

    <p class="legal-meta">
        <i class="fas fa-calendar-alt"></i> Last updated: {{ date('F j, Y') }} &nbsp;·&nbsp;
        <i class="fas fa-globe"></i> Applies to smart-trip-system.vercel.app
    </p>

    <div class="terms-highlight">
        <i class="fas fa-info-circle"></i>
        By accessing or using Smart Booking you agree to be bound by these Terms of Service. If you do not agree, please do not use the platform.
    </div>

    <div class="legal-section">
        <h2>1. Acceptance of Terms</h2>
        <p>These Terms of Service ("Terms") constitute a legally binding agreement between you and Smart Booking ("we", "our", "us"). By creating an account or using any part of our platform, you confirm that you have read, understood, and agree to these Terms.</p>
    </div>

    <div class="legal-section">
        <h2>2. Use of the Platform</h2>
        <p>You agree to use Smart Booking only for lawful purposes and in accordance with these Terms. You must not:</p>
        <ul>
            <li>Use the platform in any way that violates applicable local, national, or international law.</li>
            <li>Transmit unsolicited or unauthorised advertising material.</li>
            <li>Attempt to gain unauthorised access to any part of the platform or its related systems.</li>
            <li>Upload or transmit any malicious code, viruses, or harmful data.</li>
            <li>Impersonate another person or misrepresent your affiliation with any entity.</li>
            <li>Engage in any conduct that restricts or inhibits anyone's use or enjoyment of the platform.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>3. Accounts & Registration</h2>
        <p>To access certain features you must register for an account. You are responsible for maintaining the confidentiality of your credentials and for all activity that occurs under your account. You agree to notify us immediately of any unauthorised use of your account.</p>
        <p>We reserve the right to suspend or terminate accounts that violate these Terms or that have been inactive for an extended period.</p>
    </div>

    <div class="legal-section">
        <h2>4. Bookings & Payments</h2>
        <p>Smart Booking facilitates connections between travellers and accommodation providers. When you make a booking through the platform:</p>
        <ul>
            <li>You enter into a direct agreement with the accommodation provider.</li>
            <li>We act as an intermediary and are not a party to that agreement.</li>
            <li>Cancellation and refund policies are determined by the individual accommodation provider.</li>
            <li>All pricing is displayed in USD unless otherwise stated and is subject to change.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>5. Intellectual Property</h2>
        <p>All content on the Smart Booking platform — including text, graphics, logos, code, and data — is the property of Smart Booking or its content suppliers and is protected by applicable intellectual property laws. You may not reproduce, distribute, or create derivative works without our prior written consent.</p>
    </div>

    <div class="legal-section">
        <h2>6. AI-Generated Content</h2>
        <p>Smart Booking uses artificial intelligence to generate accommodation recommendations, match summaries, and travel insights. These are provided for informational purposes only. We do not guarantee the accuracy, completeness, or suitability of AI-generated content and recommend you verify information independently before making travel decisions.</p>
    </div>

    <div class="legal-section">
        <h2>7. Disclaimer of Warranties</h2>
        <p>The platform is provided on an "as is" and "as available" basis without warranties of any kind, either express or implied. We do not warrant that the platform will be uninterrupted, error-free, or free of viruses or other harmful components.</p>
    </div>

    <div class="legal-section">
        <h2>8. Limitation of Liability</h2>
        <p>To the fullest extent permitted by law, Smart Booking shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of, or inability to use, the platform or its content.</p>
    </div>

    <div class="legal-section">
        <h2>9. Third-Party Links & Services</h2>
        <p>The platform may contain links to third-party websites or services. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites. We encourage you to review the terms and privacy policies of any third-party services you access.</p>
    </div>

    <div class="legal-section">
        <h2>10. Changes to Terms</h2>
        <p>We reserve the right to modify these Terms at any time. We will indicate the date of the most recent revision at the top of this page. Your continued use of the platform after changes are posted constitutes your acceptance of the revised Terms.</p>
    </div>

    <div class="legal-section">
        <h2>11. Governing Law</h2>
        <p>These Terms shall be governed by and construed in accordance with the laws of Hungary, without regard to its conflict of law provisions.</p>
    </div>

    <hr class="legal-divider">

    <div class="legal-contact-box">
        <i class="fas fa-envelope"></i> <strong>Questions about these terms?</strong><br>
        Contact us at <a href="mailto:support@smartbooking.app">support@smartbooking.app</a> or visit our <a href="{{ route('contact') }}">Contact page</a>.
    </div>

</div>

@endsection