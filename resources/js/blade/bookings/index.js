function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    if (!row) return;
    row.classList.toggle('open');
    const btn = row.previousElementSibling.querySelector('[data-action*="toggleDetail"]');
    if (btn) {
        const icon = btn.querySelector('i');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
    }
}

function filterBookings(filter) {
    document.querySelectorAll('.ftab').forEach(t => t.classList.remove('active'));
    document.querySelector('[data-filter="' + filter + '"]').classList.add('active');

    document.querySelectorAll('.booking-card').forEach(card => {
        if (filter === 'all') {
            card.style.display = '';
        } else if (['confirmed', 'pending', 'cancelled', 'completed'].includes(filter)) {
            card.style.display = card.dataset.status === filter ? '' : 'none';
        } else {
            card.style.display = card.dataset.type === filter ? '' : 'none';
        }
    });
}

function searchBookings(value) {
    const q = (value || '').toLowerCase();
    document.querySelectorAll('.booking-card').forEach(card => {
        card.style.display = card.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}

function sortBookings() {
    const val = document.getElementById('sortSelect').value;
    const list = document.getElementById('demoBookings') || document.getElementById('bookingsGrid');
    const cards = [...document.querySelectorAll('.booking-card')];

    cards.sort((a, b) => {
        if (val === 'price-high') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        if (val === 'price-low')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        if (val === 'newest')     return new Date(b.dataset.date) - new Date(a.dataset.date);
        if (val === 'oldest')     return new Date(a.dataset.date) - new Date(b.dataset.date);
        return 0;
    });

    cards.forEach(c => list.appendChild(c));
}

function leaveReview(id) {
    Swal.fire({
        title: 'Leave a Review',
        html:
            '<div class="review-dialog">' +
                '<p class="review-dialog-text">How was your trip?</p>' +
                '<div class="review-stars" id="starRatingReal">' +
                    '<i class="fas fa-star review-star" data-rating="1"></i>' +
                    '<i class="fas fa-star review-star" data-rating="2"></i>' +
                    '<i class="fas fa-star review-star" data-rating="3"></i>' +
                    '<i class="fas fa-star review-star" data-rating="4"></i>' +
                    '<i class="fas fa-star review-star" data-rating="5"></i>' +
                '</div>' +
                '<textarea id="reviewComment" class="review-textarea" placeholder="Share your experience…"></textarea>' +
            '</div>',
        confirmButtonColor: '#c9a96e',
        confirmButtonText: 'Submit Review',
        showCancelButton: true,
        didOpen: () => {
            document.querySelectorAll('#starRatingReal .review-star').forEach(star => {
                star.addEventListener('click', () => setRatingReal(Number(star.dataset.rating)));
            });
        }
    }).then(r => {
        if (!r.isConfirmed) return;
        
        Swal.fire({
            title: 'Thank you!',
            text: 'Your review has been submitted.',
            icon: 'success',
            confirmButtonColor: '#c9a96e',
            timer: 2000,
            showConfirmButton: false
        });
    });
}

function setRatingReal(n) {
    document.querySelectorAll('#starRatingReal i').forEach((s, i) => {
        s.classList.toggle('selected', i < n);
    });
}

function rebookBooking(type) {
    window.location.href = type === 'hotels' ? '/accommodations' : '/flights';
}

function cancelBooking(id) {
    Swal.fire({
        title: 'Cancel Booking?',
        text: 'This action cannot be undone. Cancellation fees may apply.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#c9a96e',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'Keep Booking'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/bookings/' + id + '/cancel', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(async (r) => {
                const data = await r.json();
                if (!r.ok) {
                    throw new Error(data.message || 'Could not cancel booking');
                }
                return data;
            })
            .then((data) => {
                if (data.success) {
                    Swal.fire({ title: 'Cancelled', icon: 'success', confirmButtonColor: '#c9a96e', timer: 2000 })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#c9a96e' });
                }
            })
            .catch(() => {
                Swal.fire({ title: 'Error', text: 'Could not cancel booking', icon: 'error', confirmButtonColor: '#c9a96e' });
            });
        }
    });
}

function logout() {
    Swal.fire({
        title: 'Logout',
        text: 'Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c9a96e',
        cancelButtonColor: '#f44336',
        confirmButtonText: 'Yes, logout'
    }).then(r => {
        if (r.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    fetch('/api/user/statistics')
        .then(r => r.json())
        .then(data => {
            if (data.flights  !== undefined) document.getElementById('statFlights').textContent = data.flights;
            if (data.hotels   !== undefined) document.getElementById('statHotels').textContent  = data.hotels;
            if (data.bookings !== undefined) document.getElementById('statActive').textContent  = data.bookings;
            if (data.spent    !== undefined) document.getElementById('statSpent').textContent = (typeof window.Currency !== 'undefined' ? window.Currency.format(Number(data.spent)) : '$' + Number(data.spent).toLocaleString());
        })
        .catch(() => {});

    document.getElementById('sidebar')?.addEventListener('click', function (e) {
        if (window.innerWidth <= 768 && !e.target.closest('.sidebar')) {
            this.classList.remove('active');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    if (window.Currency) window.Currency.refresh();
});
document.addEventListener('currency:changed', function () {
    if (window.Currency) window.Currency.refresh();
});

window.toggleDetail       = toggleDetail;
window.filterBookings     = filterBookings;
window.searchBookings     = searchBookings;
window.sortBookings       = sortBookings;
window.cancelBooking      = cancelBooking;
window.leaveReview        = leaveReview;
window.rebookBooking      = rebookBooking;
window.logout             = logout;
window.setRatingReal      = setRatingReal;
