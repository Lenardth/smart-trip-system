(function () {
    function updateWishlistBadge(count) {
        document.querySelectorAll('#wishlistCount').forEach(el => {
            el.textContent = count;
        });
    }

    function loadWishlistCount() {
        fetch('/api/wishlist/count', {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => {
                if (!r.ok) return;
                return r.json();
            })
            .then(data => {
                if (data) updateWishlistBadge(data.count ?? 0);
            })
            .catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', loadWishlistCount);
})();
