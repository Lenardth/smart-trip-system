<footer class="footer">
    <div class="footer-inner">
        <div class="footer-nav-links">
            <a href="{{ url('/about') }}">About</a>
            <a href="{{ url('/contact') }}">Contact</a>
            <a href="{{ url('/privacy') }}">Privacy Policy</a>
            <a href="{{ url('/terms') }}">Terms of Service</a>
        </div>

        <div class="footer-social-links">
            <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
            <a href="#" aria-label="Laravel"><i class="fab fa-laravel"></i></a>
            <a href="#" aria-label="Docs"><i class="fas fa-graduation-cap"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>

        <p class="footer-copyright">&copy; {{ date('Y') }} Smart Booking. All rights reserved.</p>
    </div>
</footer>
