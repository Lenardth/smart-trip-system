// Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('departure_date').setAttribute('min', today);
    document.getElementById('return_date').setAttribute('min', today);

    // Trip type tabs
    document.querySelectorAll('.trip-type-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.trip-type-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.type;
            const returnDateGroup = document.getElementById('returnDateGroup');
            const returnDateInput = document.getElementById('return_date');

            if (type === 'one-way') {
                returnDateGroup.style.display = 'none';
                returnDateInput.removeAttribute('required');
            } else if (type === 'round-trip') {
                returnDateGroup.style.display = 'block';
                returnDateInput.setAttribute('required', 'required');
            } else if (type === 'multi-city') {
                Swal.fire({
                    title: 'Multi-City Flights',
                    text: 'Multi-city flight search is coming soon!',
                    icon: 'info',
                    confirmButtonColor: '#c9a96e'
                });
            }
        });
    });

    // Departure date change - update return date minimum
    document.getElementById('departure_date').addEventListener('change', function() {
        const departureDate = this.value;
        document.getElementById('return_date').setAttribute('min', departureDate);
    });

    // Fill route from popular routes
    function fillRoute(from, to) {
        document.getElementById('from').value = from;
        document.getElementById('to').value = to;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Flight search form submission
    document.getElementById('flightSearchForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const searchBtn = document.querySelector('.search-btn');
        searchBtn.classList.add('loading');

        const formData = {
            from: document.getElementById('from').value,
            to: document.getElementById('to').value,
            departure_date: document.getElementById('departure_date').value,
            return_date: document.getElementById('return_date').value,
            passengers: document.getElementById('passengers').value,
            class: document.getElementById('class').value,
        };

        try {
            const response = await fetch('/flights/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            searchBtn.classList.remove('loading');

            if (data.success) {
                displayFlights(data.flights || []);
            } else {
                Swal.fire({
                    title: 'Search Error',
                    text: data.message || 'Unable to search flights',
                    icon: 'error',
                    confirmButtonColor: '#c9a96e'
                });
            }
        } catch (error) {
            searchBtn.classList.remove('loading');
            console.error('Search error:', error);
            Swal.fire({
                title: 'Network Error',
                text: 'Could not load flights. Please try again.',
                icon: 'error',
                confirmButtonColor: '#c9a96e'
            });
        }
    });

    // Display flights
    function displayFlights(flights) {
        const resultsSection = document.getElementById('resultsSection');
        const flightResults = document.getElementById('flightResults');
        const resultsCount = document.getElementById('resultsCount');

        resultsSection.classList.add('active');
        resultsCount.textContent = flights.length;

        if (flights.length === 0) {
            flightResults.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-plane-slash"></i>
                    <h3>No Flights Found</h3>
                    <p>Try adjusting your search criteria</p>
                </div>
            `;
            return;
        }

        flightResults.innerHTML = flights.map(flight => `
            <div class="flight-card">
                <div class="flight-header">
                    <div class="airline-info">
                        <div class="airline-logo">${flight.airline_logo}</div>
                        <div class="airline-details">
                            <h4>${flight.airline}</h4>
                            <p>${flight.flight_number} • ${flight.class.replace('_', ' ').toUpperCase()}</p>
                        </div>
                    </div>
                    <div class="flight-price">
                        <div class="price">$${flight.price}</div>
                        <div class="price-label">per person</div>
                    </div>
                </div>

                <div class="flight-route">
                    <div class="route-point">
                        <div class="time">${flight.departure_time}</div>
                        <div class="airport-code">${(flight.from_code || flight.from.substring(0, 3)).toUpperCase()}</div>
                        <div class="location">${flight.from}</div>
                    </div>
                    <div class="route-divider">
                        <div class="route-line"></div>
                        <div class="route-icon"><i class="fas fa-plane"></i></div>
                        <div class="route-duration">${flight.duration} ${flight.stops === 0 ? '• Direct' : '• ' + flight.stops + ' stop'}</div>
                    </div>
                    <div class="route-point">
                        <div class="time">${flight.arrival_time}</div>
                        <div class="airport-code">${(flight.to_code || flight.to.substring(0, 3)).toUpperCase()}</div>
                        <div class="location">${flight.to}</div>
                    </div>
                </div>

                <div class="flight-details">
                    <div class="detail-item">
                        <i class="fas fa-suitcase"></i>
                        <span><strong>Baggage:</strong> ${flight.baggage}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-chair"></i>
                        <span><strong>Seats:</strong> ${flight.seats_available} available</span>
                    </div>
                    ${flight.amenities ? flight.amenities.map(a => `
                        <div class="detail-item">
                            <i class="fas fa-check-circle"></i>
                            <span>${a}</span>
                        </div>
                    `).join('') : ''}
                </div>

                <div class="flight-tags">
                    ${flight.stops === 0 ? '<span class="flight-tag">Direct Flight</span>' : ''}
                    ${flight.seats_available < 5 ? '<span class="flight-tag warning">Only ' + flight.seats_available + ' seats left</span>' : ''}
                    ${flight.amenities && flight.amenities.includes('WiFi') ? '<span class="flight-tag info">WiFi Available</span>' : ''}
                </div>

                <button class="book-btn" onclick="bookFlight('${flight.id}', '${flight.airline}', ${flight.price})">
                    <i class="fas fa-ticket-alt"></i> Book Now - $${flight.price}
                </button>
            </div>
        `).join('');

        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Book flight
    async function bookFlight(flightId, airline, price) {
        const result = await Swal.fire({
            title: 'Book Flight',
            html: `
                <div style="text-align: left; padding: 20px;">
                    <h4 style="margin-bottom: 15px;">Flight Details</h4>
                    <p><strong>Airline:</strong> ${airline}</p>
                    <p><strong>Flight ID:</strong> ${flightId}</p>
                    <p><strong>Total Price:</strong> $${price}</p>
                    <hr style="margin: 20px 0;">
                    <p style="color: #6b5b4f; font-size: 14px;">
                        <i class="fas fa-info-circle"></i>
                        You will be redirected to complete passenger details and payment.
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Continue to Booking',
            cancelButtonText: 'Cancel'
        });
        if (!result.isConfirmed) return;
        try {
            const response = await fetch(`/flights/${flightId}/book`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ seats: 1 })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Could not complete booking.');
            }
            await Swal.fire({
                title: 'Booked!',
                text: data.message || 'Your flight is booked.',
                icon: 'success',
                confirmButtonColor: '#c9a96e'
            });
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        } catch (err) {
            Swal.fire({
                title: 'Booking Error',
                text: err.message || 'Unable to book this flight now.',
                icon: 'error',
                confirmButtonColor: '#c9a96e'
            });
        }
    }

    // Sort flights
    document.getElementById('sortBy').addEventListener('change', function() {
        Swal.fire({
            title: 'Sorting...',
            text: 'Reordering flights by ' + this.options[this.selectedIndex].text,
            icon: 'info',
            confirmButtonColor: '#c9a96e',
            timer: 1500,
            showConfirmButton: false
        });
    });

(function () {
    function updateWishlistBadge(count) {
        document.querySelectorAll('#wishlistCount').forEach(el => {
            el.textContent = count;
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        fetch('/api/wishlist/count', { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => { if (data) updateWishlistBadge(data.count ?? 0); })
            .catch(() => {});
    });
})();

// Expose handlers for Blade inline onclick attributes.
window.fillRoute = fillRoute;
window.bookFlight = bookFlight;
