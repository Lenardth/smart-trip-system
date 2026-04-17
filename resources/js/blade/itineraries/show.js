// Itinerary Show Page

function deleteItinerary(id) {
    const doDelete = () => {
        fetch('/itineraries/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(r => r.json()).then(d => {
            if (d.success) window.location.href = '/itineraries';
        });
    };

    if (typeof Swal === 'undefined') {
        if (confirm('Delete this itinerary?')) doDelete();
    } else {
        Swal.fire({
            title: 'Delete Itinerary?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f44336',
            confirmButtonText: 'Delete',
        }).then(r => { if (r.isConfirmed) doDelete(); });
    }
}

window.deleteItinerary = deleteItinerary;
