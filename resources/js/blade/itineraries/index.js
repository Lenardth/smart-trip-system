// Itineraries Module

(function () {
    const path = window.location.pathname;
    
    // Check if we're on a show page (e.g., /itineraries/123)
    if (path.match(/^\/itineraries\/\d+/)) {
        // Load show page module
        import('./show.js').catch(err => console.error('[itineraries] show module failed:', err));
    } else {
        // Index page functionality
        function filterItineraries() {
            const q = document.getElementById('itinSearch').value.toLowerCase();
            document.querySelectorAll('.itin-card').forEach(c => {
                c.style.display = !q || c.dataset.search.includes(q) ? '' : 'none';
            });
        }

        function deleteItinerary(id, name) {
            const doDelete = () => {
                fetch('/itineraries/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                }).then(r => r.json()).then(d => {
                    if (d.success) location.reload();
                });
            };

            if (typeof Swal === 'undefined') {
                if (confirm('Delete itinerary for ' + name + '?')) doDelete();
            } else {
                Swal.fire({
                    title: 'Delete Itinerary?',
                    text: name,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f44336',
                    cancelButtonColor: '#6b5b4f',
                    confirmButtonText: 'Delete',
                }).then(r => { if (r.isConfirmed) doDelete(); });
            }
        }

        window.filterItineraries = filterItineraries;
        window.deleteItinerary = deleteItinerary;
    }
})();
