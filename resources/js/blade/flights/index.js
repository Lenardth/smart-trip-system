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
                displayFlights(data.flights || generateMockFlights(formData));
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

            // Show mock results for demo
            displayFlights(generateMockFlights(formData));
        }
    });

    // Generate mock flight data for demo
    function generateMockFlights(searchData) {
        const airlines = [
            { name: 'Emirates', code: 'EK', logo: '✈️' },
            { name: 'Qatar Airways', code: 'QR', logo: '🛫' },
            { name: 'Singapore Airlines', code: 'SQ', logo: '🛬' },
            { name: 'Lufthansa', code: 'LH', logo: '✈️' },
            { name: 'British Airways', code: 'BA', logo: '🛫' }
        ];

        const flights = [];
        const basePrice = 300 + Math.random() * 400;

        for (let i = 0; i < 5; i++) {
            const airline = airlines[i % airlines.length];
            const departureHour = 6 + Math.floor(Math.random() * 12);
            const duration = 3 + Math.floor(Math.random() * 8);
            const stops = Math.random() > 0.6 ? 0 : 1;

            flights.push({
                id: `FL${1000 + i}`,
                airline: airline.name,
                airline_code: airline.code,
                airline_logo: airline.logo,
                flight_number: `${airline.code}${100 + i}`,
                from: searchData.from,
                to: searchData.to,
                departure_time: `${departureHour.toString().padStart(2, '0')}:${(Math.random() * 60).toFixed(0).padStart(2, '0')}`,
                arrival_time: `${((departureHour + duration) % 24).toString().padStart(2, '0')}:${(Math.random() * 60).toFixed(0).padStart(2, '0')}`,
                duration: `${duration}h ${(Math.random() * 60).toFixed(0)}m`,
                stops: stops,
                price: (basePrice + i * 50).toFixed(2),
                class: searchData.class,
                seats_available: 5 + Math.floor(Math.random() * 15),
                baggage: stops === 0 ? '2 x 23kg' : '1 x 23kg',
                amenities: stops === 0 ? ['WiFi', 'Meals', 'Entertainment'] : ['Meals', 'Entertainment']
            });
        }

        return flights;
    }

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
                        <div class="airport-code">${flight.from.substring(0, 3).toUpperCase()}</div>
                        <div class="location">${flight.from}</div>
                    </div>
                    <div class="route-divider">
                        <div class="route-line"></div>
                        <div class="route-icon"><i class="fas fa-plane"></i></div>
                        <div class="route-duration">${flight.duration} ${flight.stops === 0 ? '• Direct' : '• ' + flight.stops + ' stop'}</div>
                    </div>
                    <div class="route-point">
                        <div class="time">${flight.arrival_time}</div>
                        <div class="airport-code">${flight.to.substring(0, 3).toUpperCase()}</div>
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
    function bookFlight(flightId, airline, price) {

        Swal.fire({
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
        }).then((result) => {
            if (result.isConfirmed) {
                // In production, redirect to booking page
                Swal.fire({
                    title: 'Processing...',
                    html: 'Redirecting to booking page',
                    icon: 'success',
                    confirmButtonColor: '#c9a96e',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    // window.location.href = `/flights/book/${flightId}`;
                    console.log('Booking flight:', flightId);
                });
            }
        });

        Swal.fire({
            title: 'Login Required',
            text: 'Please log in to book flights',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Go to Login',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/login';
            }
        });

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
