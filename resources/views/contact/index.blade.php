@extends('layouts.public')

@section('title', 'Contact — Smart Booking')

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .static-hero {
            padding: 5rem 2rem 3.5rem;
            background: linear-gradient(160deg, rgba(80,40,10,0.72) 0%, rgba(20,10,5,0.55) 100%),
                        url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1800&q=80') center/cover no-repeat;
            text-align: center;
            color: #fff;
        }
        .static-hero h1 { font-size: 2.4rem; margin: 0 0 .6rem; letter-spacing: .03em; }
        .static-hero p  { font-size: 1.05rem; opacity: .85; max-width: 540px; margin: 0 auto; }

        .contact-wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 5rem;
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 2.5rem;
            align-items: start;
        }

        @media (max-width: 640px) {
            .contact-wrap { grid-template-columns: 1fr; }
        }

        .contact-info h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--deep, #1a0a00);
            border-left: 3px solid var(--gold, #b8860b);
            padding-left: .75rem;
            margin: 0 0 1.25rem;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: 1.1rem;
            font-size: .92rem;
            color: var(--text-sub, #333);
            line-height: 1.5;
        }

        .contact-item i {
            color: var(--gold, #b8860b);
            font-size: 1rem;
            margin-top: .15rem;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
        }

        .contact-item a {
            color: var(--gold, #b8860b);
            text-decoration: none;
        }

        .contact-item a:hover { text-decoration: underline; }

        .contact-form-card {
            background: var(--card-bg, #fffdf7);
            border: 1px solid var(--border, #e8dcc8);
            border-radius: 10px;
            padding: 2rem;
        }

        .contact-form-card h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--deep, #1a0a00);
            border-left: 3px solid var(--gold, #b8860b);
            padding-left: .75rem;
            margin: 0 0 1.5rem;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-group label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--deep, #1a0a00);
            margin-bottom: .35rem;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: .65rem .85rem;
            border: 1px solid var(--border, #e8dcc8);
            border-radius: 6px;
            background: #fff;
            font-size: .9rem;
            color: var(--deep, #1a0a00);
            font-family: inherit;
            transition: border-color .2s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold, #b8860b);
        }

        .form-group textarea { resize: vertical; min-height: 120px; }

        .form-submit {
            width: 100%;
            padding: .75rem;
            background: var(--gold, #b8860b);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: .03em;
            transition: background .2s;
            font-family: inherit;
        }

        .form-submit:hover { background: var(--gold-hover, #9a7209); }

        .alert-success {
            background: #f0faf0;
            border: 1px solid #7cb97c;
            border-radius: 6px;
            padding: .75rem 1rem;
            color: #2d6a2d;
            font-size: .88rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .alert-error {
            background: #fdf0f0;
            border: 1px solid #d47c7c;
            border-radius: 6px;
            padding: .75rem 1rem;
            color: #8b2020;
            font-size: .88rem;
            margin-bottom: 1.25rem;
        }
    </style>
@endpush

@section('content')

<section class="static-hero">
    <h1><i class="fas fa-envelope-open-text"></i> Get In Touch</h1>
    <p>Have a question, suggestion, or partnership inquiry? We'd love to hear from you.</p>
</section>

<div class="contact-wrap">

    <div class="contact-info">
        <h2>Contact Details</h2>

        <div class="contact-item">
            <i class="fas fa-envelope"></i>
            <div>
                <strong>Email</strong><br>
                <a href="mailto:support@smartbooking.app">support@smartbooking.app</a>
            </div>
        </div>

        <div class="contact-item">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <strong>Address</strong><br>
                Budapest University of Technology<br>
                and Economics, Budapest, Hungary
            </div>
        </div>

        <div class="contact-item">
            <i class="fas fa-clock"></i>
            <div>
                <strong>Response Time</strong><br>
                We typically respond within 24–48 hours on business days.
            </div>
        </div>

        <div class="contact-item">
            <i class="fab fa-github"></i>
            <div>
                <strong>GitHub</strong><br>
                <a href="https://github.com/Lenardth" target="_blank" rel="noopener">github.com/Lenardth</a>
            </div>
        </div>
    </div>

    <div class="contact-form-card">
        <h2>Send a Message</h2>

        @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('contact.send') }}">
            @csrf

            <div class="form-group">
                <label for="contact_name">Full Name</label>
                <input id="contact_name" type="text" name="name" value="{{ old('name') }}" placeholder="Your name" required>
            </div>

            <div class="form-group">
                <label for="contact_email">Email Address</label>
                <input id="contact_email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="contact_subject">Subject</label>
                <select id="contact_subject" name="subject">
                    <option value="general">General Enquiry</option>
                    <option value="support">Technical Support</option>
                    <option value="booking">Booking Issue</option>
                    <option value="partnership">Partnership</option>
                    <option value="feedback">Feedback</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="contact_message">Message</label>
                <textarea id="contact_message" name="message" placeholder="Tell us how we can help…" required>{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="form-submit">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>

</div>

@endsection
