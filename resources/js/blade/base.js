(function () {
    function toggleSidebar() {
        var sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.classList.toggle('active');
    }

    function updateWishlistBadge(count) {
        document.querySelectorAll('#wishlistCount, #savedCount, #statSavedCount').forEach(function (el) {
            el.textContent = count;
        });
    }

    function loadWishlistCount() {
        fetch('/api/wishlist/count', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) return null; return r.json(); })
            .then(function (data) { if (data) updateWishlistBadge(data.count ?? 0); })
            .catch(function () {});
    }

    window.togglePublicNav = function () {
        var nav = document.getElementById('publicNav');
        if (nav) nav.classList.toggle('open');
    };

    window.toggleSidebar = window.toggleSidebar || toggleSidebar;

    document.addEventListener('DOMContentLoaded', loadWishlistCount);

    window.addEventListener('storage', function (e) {
        if (e.key !== 'smartBookingWishlistUpdated' || !e.newValue) return;
        loadWishlistCount();
    });

    // Also expose so wishlist toggles on any page can call it directly
    window.__refreshWishlistBadge = loadWishlistCount;
})();
