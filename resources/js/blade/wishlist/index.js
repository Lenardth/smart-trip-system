const Wishlist = (() => {

    const CSRF = () => document.querySelector('meta[name="csrf-token"]').content;

    

    function apiRemove(destinationId) {
        return fetch(`/wishlist/${destinationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF(),
                'Content-Type': 'application/json'
            }
        }).then(r => r.json());
    }

    

    function setCardVisible(card, visible) {
        card.style.display = visible ? 'block' : 'none';
    }

    function showNoResults() {
        Swal.fire({
            title: 'No Results',
            text: 'No destinations match your filters',
            icon: 'info',
            confirmButtonColor: '#c9a96e'
        });
    }

    

    function filter() {
        const continent = document.getElementById('filterContinent').value;
        const category  = document.getElementById('filterCategory').value;
        const search    = document.getElementById('searchWishlist').value.toLowerCase();

        let visibleCount = 0;

        document.querySelectorAll('.wishlist-card').forEach(card => {
            const visible =
                (continent === 'all' || card.dataset.continent === continent) &&
                (category  === 'all' || card.dataset.category  === category)  &&
                (search    === ''    || card.dataset.name.includes(search));

            setCardVisible(card, visible);
            if (visible) visibleCount++;
        });

        const filtersActive = continent !== 'all' || category !== 'all' || search !== '';
        if (visibleCount === 0 && filtersActive) showNoResults();
    }

    async function remove(destinationId, destinationName) {
        const result = await Swal.fire({
            title: 'Remove from Wishlist?',
            text: `Remove ${destinationName} from your wishlist?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) return;

        try {
            const data = await apiRemove(destinationId);
            if (!data.success) throw new Error(data.message);
            await Swal.fire({
                title: 'Removed!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#c9a96e',
                timer: 2000
            });
            
            
            
            
            
            try {
                localStorage.setItem('smartBookingWishlistUpdated', String(Date.now()));
            } catch (_) {}
            
            window.location.reload();
        } catch {
            Swal.fire({
                title: 'Error',
                text: 'Failed to remove from wishlist',
                icon: 'error',
                confirmButtonColor: '#c9a96e'
            });
        }
    }

    async function clearAll() {
        const result = await Swal.fire({
            title: 'Clear All?',
            text: 'This will remove all destinations from your wishlist',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, clear all',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            Swal.fire({
                title: 'Feature Coming Soon',
                text: 'Bulk remove functionality will be available soon',
                icon: 'info',
                confirmButtonColor: '#c9a96e'
            });
        }
    }

    function logout() {
        Swal.fire({
            title: 'Logout',
            text: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#f44336',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (!result.isConfirmed) return;
            const form  = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            const csrf  = document.createElement('input');
            csrf.type   = 'hidden';
            csrf.name   = '_token';
            csrf.value  = CSRF();
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        });
    }

    function planTrip(destinationId, destinationName) {
        Swal.fire({
            title: 'Plan Your Trip',
            text: `Ready to plan your trip to ${destinationName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: "Yes, let's go!",
            cancelButtonText: 'Not yet'
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = `/plan-trip?destination=${destinationId}`;
            }
        });
    }

    

    function init() {
        document.getElementById('filterContinent')?.addEventListener('change', filter);
        document.getElementById('filterCategory')?.addEventListener('change', filter);
        document.getElementById('searchWishlist')?.addEventListener('input', filter);
    }

    document.addEventListener('DOMContentLoaded', init);

    return { filter, remove, clearAll, planTrip, logout };

})();

window.Wishlist = Wishlist;