@extends('layouts.public')

@section('title', 'About — Smart Booking')

@push('styles')
    @vite(['resources/css/blade/base.css'])
    <style>
        .static-hero {
            padding: 5rem 2rem 3.5rem;
            background: linear-gradient(160deg, rgba(80,40,10,0.72) 0%, rgba(20,10,5,0.55) 100%),
                        url('https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1800&q=80') center/cover no-repeat;
            text-align: center;
            color: #fff;
        }
        .static-hero h1 { font-size: 2.4rem; margin: 0 0 .6rem; letter-spacing: .03em; }
        .static-hero p  { font-size: 1.05rem; opacity: .85; max-width: 540px; margin: 0 auto; }

        .static-body {
            max-width: 860px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 5rem;
            color: var(--text-sub, #333);
            line-height: 1.8;
        }

        .about-section {
            margin-bottom: 3rem;
        }

        .about-section h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--deep, #1a0a00);
            border-left: 3px solid var(--gold, #b8860b);
            padding-left: .75rem;
            margin-bottom: 1rem;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .about-section p {
            margin: 0 0 .9rem;
            font-size: .97rem;
        }

        .about-pillars {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-top: 1.25rem;
        }

        .pillar {
            background: var(--card-bg, #fffdf7);
            border: 1px solid var(--border, #e8dcc8);
            border-top: 3px solid var(--gold, #b8860b);
            border-radius: 6px;
            padding: 1.25rem 1rem;
            text-align: center;
        }

        .pillar i {
            font-size: 1.6rem;
            color: var(--gold, #b8860b);
            margin-bottom: .6rem;
            display: block;
        }

        .pillar strong {
            display: block;
            font-size: .9rem;
            font-weight: 700;
            color: var(--deep, #1a0a00);
            margin-bottom: .35rem;
        }

        .pillar span {
            font-size: .82rem;
            color: var(--text-muted, #888);
            line-height: 1.5;
        }

        .about-team {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-top: 1.25rem;
        }

        .team-card {
            background: var(--card-bg, #fffdf7);
            border: 1px solid var(--border, #e8dcc8);
            border-radius: 8px;
            padding: 1.25rem 1rem;
            text-align: center;
        }

        .team-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--gold, #b8860b);
            color: #fff;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto .75rem;
        }

        .team-card strong {
            display: block;
            font-size: .88rem;
            font-weight: 700;
            color: var(--deep, #1a0a00);
        }

        .team-card span {
            font-size: .78rem;
            color: var(--text-muted, #888);
        }
    </style>
@endpush

@section('content')

<section class="static-hero">
    <h1><i class="fas fa-compass"></i> About Smart Booking</h1>
    <p>Your AI-powered companion for seamless travel planning and accommodation.</p>
</section>

<div class="static-body">

    <div class="about-section">
        <h2>Our Mission</h2>
        <p>Smart Booking was built to take the friction out of travel planning. We combine real-time accommodation data, intelligent matching, and live destination insights so that every trip you plan feels effortless — whether you're a backpacker on a shoestring or a traveller seeking luxury.</p>
        <p>We believe great travel starts with great information. That's why every search surfaces not just places to stay, but local context — news, maps, and neighbourhood vibes — so you arrive prepared and confident.</p>
    </div>

    <div class="about-section">
        <h2>What We Offer</h2>
        <div class="about-pillars">
            <div class="pillar">
                <i class="fas fa-wand-magic-sparkles"></i>
                <strong>AI Matching</strong>
                <span>Smart recommendations tailored to your travel style and budget.</span>
            </div>
            <div class="pillar">
                <i class="fas fa-map-marked-alt"></i>
                <strong>Live Maps</strong>
                <span>Interactive pin maps so you know exactly where you'll be staying.</span>
            </div>
            <div class="pillar">
                <i class="fas fa-newspaper"></i>
                <strong>Local News</strong>
                <span>Real-time destination headlines to keep you informed before you arrive.</span>
            </div>
            <div class="pillar">
                <i class="fas fa-comments"></i>
                <strong>Real-time Chat</strong>
                <span>Message hosts and agents instantly with Pusher-powered messaging.</span>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2>Built With Care</h2>
        <p>Smart Booking is a full-stack platform built on Laravel 11, powered by a 14-table relational database, real-time Pusher messaging, and a RESTful API. It was developed as a thesis project at the Budapest University of Technology and Economics, with a focus on clean architecture, robust CI/CD pipelines, and a genuine user-first experience.</p>
    </div>

    <div class="about-section">
        <h2>The Team</h2>
        <div class="about-team">
            <div class="team-card">
                <div class="team-avatar"><i class="fas fa-user"></i></div>
                <strong>Lenard T. Hlabangwana</strong>
                <span>Founder &amp; Developer</span>
            </div>
        </div>
    </div>

</div>

@endsection
