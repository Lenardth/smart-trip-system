(function () {
    function togglePublicNav() {
        var nav = document.getElementById("publicNav");
        if (!nav) return;
        nav.classList.toggle("open");
    }

    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        if (!sidebar) return;
        sidebar.classList.toggle("active");
    }

    document.addEventListener("click", function (event) {
        var nav = document.getElementById("publicNav");
        var navToggle = document.querySelector(".public-nav-toggle");
        if (nav && nav.classList.contains("open") && navToggle) {
            if (!nav.contains(event.target) && !navToggle.contains(event.target)) {
                nav.classList.remove("open");
            }
        }
    });

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

    window.togglePublicNav = togglePublicNav;
    window.toggleSidebar = window.toggleSidebar || toggleSidebar;
    document.addEventListener('DOMContentLoaded', loadWishlistCount);
})();
