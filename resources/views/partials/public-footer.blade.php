<footer class="site-footer">
    <div class="footer-top">
        <div class="footer-brand-col">
            <a href="{{ url('/') }}" class="footer-brand">
                <img src="{{ asset('img/logo.png') }}" alt="Smart Booking" class="footer-logo">
                <span class="footer-brand-name">Smart <span>Booking</span></span>
            </a>
            <p class="footer-tagline">AI-powered travel planning. Find flights, stays, and personalised trip ideas — all in one place.</p>
            <div class="footer-social">
                <a href="#" class="footer-social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" class="footer-social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="footer-social-link" aria-label="GitHub"><i class="fab fa-github"></i></a>
            </div>
        </div>

        <div class="footer-links-col">
            <h4 class="footer-col-title">Features</h4>
            <ul class="footer-link-list">
                <li><a href="{{ route('plan-trip') }}"><i class="fas fa-route"></i> Plan a Trip</a></li>
                <li><a href="{{ route('flights.index') }}"><i class="fas fa-plane"></i> Search Flights</a></li>
                <li><a href="{{ route('accommodations.index') }}"><i class="fas fa-hotel"></i> Find Stays</a></li>
                @auth
                <li><a href="{{ route('bookings.index') }}"><i class="fas fa-ticket-alt"></i> My Bookings</a></li>
                @endauth
            </ul>
        </div>

        <div class="footer-links-col">
            <h4 class="footer-col-title">Company</h4>
            <ul class="footer-link-list">
                <li><a href="{{ route('about') }}"><i class="fas fa-info-circle"></i> About Us</a></li>
                <li><a href="{{ route('contact') }}"><i class="fas fa-envelope"></i> Contact</a></li>
                <li><a href="{{ route('privacy') }}"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                <li><a href="{{ route('terms') }}"><i class="fas fa-file-contract"></i> Terms of Service</a></li>
            </ul>
        </div>

        <div class="footer-links-col">
            <h4 class="footer-col-title">Account</h4>
            <ul class="footer-link-list">
                @guest
                    <li><a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Sign In</a></li>
                    <li><a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Create Account</a></li>
                @else
                    <li><a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="footer-logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Sign Out
                            </button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p class="footer-copy">&copy; {{ date('Y') }} Smart Booking. All rights reserved.</p>
        <p class="footer-tech">Built with <i class="fab fa-laravel"></i> Laravel &amp; <i class="fas fa-robot"></i> Groq AI</p>
    </div>
</footer>
