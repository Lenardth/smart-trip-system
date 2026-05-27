<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" class="footer-logo">
            <p class="footer-tagline">Smart travel planning powered by AI.</p>
        </div>

        <div class="footer-links">
            <div class="footer-col">
                <h4>Features</h4>
                <a href="{{ route('plan-trip') }}">Plan a Trip</a>
                <a href="{{ route('flights.index') }}">Search Flights</a>
                <a href="{{ route('accommodations.index') }}">Find Hotels</a>
                <a href="{{ route('bookings.index') }}">My Bookings</a>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <a href="{{ route('about') }}">About Us</a>
                <a href="{{ route('contact') }}">Contact</a>
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms of Service</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                @auth
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <a href="{{ route('dashboard', ['tab' => 'settings']) }}">Settings</a>
                    <form method="POST" action="{{ route('logout') }}" class="footer-logout-form">
                        @csrf
                        <button type="submit" class="footer-link-btn">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Sign In</a>
                    <a href="{{ route('register') }}">Create Account</a>
                @endauth
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</footer>
