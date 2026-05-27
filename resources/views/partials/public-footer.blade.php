<footer class="footer">
    <div class="footer-inner">
        <div class="footer-nav-links">
            <a href="{{ url('/about') }}">About</a>
            <a href="{{ url('/contact') }}">Contact</a>
            <a href="{{ url('/privacy') }}">Privacy Policy</a>
            <a href="{{ url('/terms') }}">Terms of Service</a>
        </div>

        <div class="footer-social-links">
            <a href="{{ route('discover') }}" aria-label="Discover destinations"><i class="fas fa-compass"></i></a>
            <a href="{{ route('plan-trip') }}" aria-label="Plan a trip"><i class="fas fa-route"></i></a>
            <a href="{{ route('flights.index') }}" aria-label="Search flights"><i class="fas fa-plane"></i></a>
            <a href="{{ route('accommodations.index') }}" aria-label="Find stays"><i class="fas fa-hotel"></i></a>
            <a href="{{ route('contact') }}" aria-label="Contact Smart Booking"><i class="fas fa-envelope"></i></a>
        </div>

        <p class="footer-copyright">&copy; {{ date('Y') }} Smart Booking. All rights reserved.</p>
    </div>
</footer>
