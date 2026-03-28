<footer class="footer">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">

        <div style="margin-bottom: 14px; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
            <a href="{{ url('/about') }}">About</a>
            <a href="{{ url('/contact') }}">Contact</a>
            <a href="{{ url('/privacy') }}">Privacy Policy</a>
            <a href="{{ url('/terms') }}">Terms of Service</a>
        </div>

        <div style="margin-bottom: 14px; display: flex; justify-content: center; gap: 16px;">
            <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
            <a href="#" aria-label="Laravel"><i class="fab fa-laravel"></i></a>
            <a href="#" aria-label="Docs"><i class="fas fa-graduation-cap"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>

        <p style="margin: 0;">© {{ date('Y') }} Smart Booking. All rights reserved.</p>

    </div>
</footer>
