function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    if (!row) return;
    row.classList.toggle('open');
    const btn = row.previousElementSibling.querySelector('[onclick*="toggleDetail"]');
    if (btn) {
        const icon = btn.querySelector('i');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
    }
}

function toggleDetailDemo(id) {
    const row = document.getElementById('detail-' + id);
    if (!row) return;
    row.classList.toggle('open');
    const btn = row.previousElementSibling.querySelector('[onclick*="toggleDetailDemo"]');
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

function searchBookings(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('.booking-card').forEach(card => {
        card.style.display = card.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}

function sortBookings() {
    const val = document.getElementById('sortSelect').value;
    const list = document.getElementById('demoBookings') || document.getElementById('bookingsList');
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

function downloadTicketDemo() {
    Swal.fire({
        title: 'Downloading Ticket',
        text: 'Your e-ticket is being prepared…',
        icon: 'success',
        confirmButtonColor: '#c9a96e',
        timer: 2000,
        showConfirmButton: false
    });
}

function cancelDemo() {
    Swal.fire({
        title: 'Cancel Booking?',
        text: 'This action cannot be undone. Cancellation fees may apply.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#c9a96e',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'Keep Booking'
    }).then(r => {
        if (r.isConfirmed) {
            Swal.fire({ title: 'Booking Cancelled', icon: 'success', confirmButtonColor: '#c9a96e' });
        }
    });
}

function rebookDemo() {
    window.location.href = '/flights';
}

function leaveReviewDemo() {
    Swal.fire({
        title: 'Leave a Review',
        html: `
            <div style="text-align:left;padding:10px 0;">
                <p style="margin-bottom:12px;color:#6b5b4f;">How was your Bali trip?</p>
                <div style="display:flex;gap:6px;font-size:28px;margin-bottom:16px;justify-content:center;" id="starRating">
                    <i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRating(1)"></i>
                    <i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRating(2)"></i>
                    <i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRating(3)"></i>
                    <i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRating(4)"></i>
                    <i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRating(5)"></i>
                </div>
                <textarea style="width:100%;min-height:100px;padding:10px;border:1px solid #e2d5c7;border-radius:8px;font-family:inherit;font-size:14px;" placeholder="Share your experience…"></textarea>
            </div>`,
        confirmButtonColor: '#c9a96e',
        confirmButtonText: 'Submit Review',
        showCancelButton: true
    });
}

function leaveReview(id) {
    Swal.fire({
        title: 'Leave a Review',
        html:
            '<div style="text-align:left;padding:10px 0;">' +
                '<p style="margin-bottom:12px;color:#6b5b4f;">How was your trip?</p>' +
                '<div style="display:flex;gap:6px;font-size:28px;margin-bottom:16px;justify-content:center;" id="starRatingReal">' +
                    '<i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRatingReal(1)"></i>' +
                    '<i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRatingReal(2)"></i>' +
                    '<i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRatingReal(3)"></i>' +
                    '<i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRatingReal(4)"></i>' +
                    '<i class="fas fa-star" style="color:#e0e0e0;cursor:pointer;" onclick="setRatingReal(5)"></i>' +
                '</div>' +
                '<textarea id="reviewComment" style="width:100%;min-height:100px;padding:10px;border:1px solid #e2d5c7;border-radius:8px;font-family:inherit;font-size:14px;" placeholder="Share your experience…"></textarea>' +
            '</div>',
        confirmButtonColor: '#c9a96e',
        confirmButtonText: 'Submit Review',
        showCancelButton: true
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
        s.style.color = i < n ? '#c9a96e' : '#e0e0e0';
    });
}

function rebookBooking(id) {
    
    window.location.href = '/flights';
}

function setRating(n) {
    document.querySelectorAll('#starRating i').forEach((s, i) => {
        s.style.color = i < n ? '#c9a96e' : '#e0e0e0';
    });
}

function downloadTicket(id) {
    window.location.href = '/bookings/' + id + '/ticket';
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
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
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

window.toggleDetail       = toggleDetail;
window.toggleDetailDemo   = toggleDetailDemo;
window.filterBookings     = filterBookings;
window.searchBookings     = searchBookings;
window.sortBookings       = sortBookings;
window.downloadTicketDemo = downloadTicketDemo;
window.cancelDemo         = cancelDemo;
window.rebookDemo         = rebookDemo;
window.leaveReviewDemo    = leaveReviewDemo;
window.setRating          = setRating;
window.downloadTicket     = downloadTicket;
window.cancelBooking      = cancelBooking;
window.leaveReview        = leaveReview;
window.rebookBooking      = rebookBooking;
window.logout             = logout;
window.setRatingReal      = setRatingReal;