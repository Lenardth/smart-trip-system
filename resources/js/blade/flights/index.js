let currentFlights = [];

// Top-level window proxies so onclick attributes work before ready() fires
window.fillRoute    = function(from, to) { if (window._fillRoute)    window._fillRoute(from, to); };
window.bookFlight   = function(idx)      { if (window._bookFlight)   window._bookFlight(idx); };
window.searchFlights= function()         { if (window._searchFlights) window._searchFlights(); };

function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
}

function getCsrfToken() {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : '';
}

function escapeHtml(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = String(text);
    return d.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const d = new Date(dateString);
    return isNaN(d) ? dateString : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function getDurationMinutes(duration) {
    if (!duration) return 999999;
    const m = duration.match(/(\d+)\s*h(?:\s*(\d+)\s*m)?/i);
    return m ? (parseInt(m[1]) || 0) * 60 + (parseInt(m[2]) || 0) : 999999;
}

function fmt(n) {
    return '$' + Number(n).toLocaleString();
}

ready(function () {
    const form               = document.getElementById('flightSearchForm');
    const fromInput          = document.getElementById('from');
    const toInput            = document.getElementById('to');
    const departureDateInput = document.getElementById('departure_date');
    const returnDateInput    = document.getElementById('return_date');
    const passengersInput    = document.getElementById('passengers');
    const classSelect        = document.getElementById('class');
    const resultsSection     = document.getElementById('resultsSection');
    const flightResults      = document.getElementById('flightResults');
    const resultsCountSpan   = document.getElementById('resultsCount');
    const sortBySelect       = document.getElementById('sortBy');
    const searchBtn          = document.querySelector('.search-btn');

    const today = new Date().toISOString().split('T')[0];
    if (departureDateInput) departureDateInput.min = today;
    if (returnDateInput)    returnDateInput.min    = today;

    // Swap airports button
    const swapBtn = document.getElementById('swapBtn');
    if (swapBtn) {
        swapBtn.addEventListener('click', function() {
            const fromVal = fromInput ? fromInput.value : '';
            const toVal   = toInput   ? toInput.value   : '';
            if (fromInput) fromInput.value = toVal;
            if (toInput)   toInput.value   = fromVal;
        });
    }

    let currentTripType = 'round-trip';

    document.querySelectorAll('.trip-type-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.trip-type-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentTripType = this.dataset.type;
            const rg = document.getElementById('returnDateGroup');
            if (rg) rg.style.display = currentTripType === 'one-way' ? 'none' : 'block';
        });
    });

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            await searchFlights();
        });
    }

    if (sortBySelect) {
        sortBySelect.addEventListener('change', function () {
            if (currentFlights.length > 0) displayFlights(currentFlights);
        });
    }

    window.fillRoute = function (from, to) {
        if (fromInput) fromInput.value = from;
        if (toInput)   toInput.value   = to;
        setTimeout(function() { searchFlights(); }, 100);
    };

    async function searchFlights() {
        if (!fromInput || !fromInput.value.trim())   { showError('Please enter departure city or airport'); return; }
        if (!toInput   || !toInput.value.trim())     { showError('Please enter arrival city or airport');   return; }
        if (!departureDateInput || !departureDateInput.value) { showError('Please select departure date'); return; }

        const requestData = {
            from:           fromInput.value.trim(),
            to:             toInput.value.trim(),
            departure_date: departureDateInput.value,
            adults:         parseInt(passengersInput ? passengersInput.value : 1) || 1,
            travel_class:   classSelect ? classSelect.value.toUpperCase() : 'ECONOMY',
        };
        if (currentTripType === 'round-trip' && returnDateInput && returnDateInput.value) {
            requestData.return_date = returnDateInput.value;
        }

        setLoading(true);
        try {
            const response = await fetch('/flights/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData),
            });
            const result = await response.json();
            if (result.success) {
                if (result.flights && result.flights.length > 0) {
                    currentFlights = result.flights;
                    displayFlights(currentFlights);
                    showResultsSection(true);
                } else {
                    showNoResults(result.message || 'No flights found. Try different dates or destinations.');
                }
            } else {
                showError(result.message || 'Failed to search flights. Please try again.');
            }
        } catch (err) {
            showError('Network error. Please check your connection and try again.');
        } finally {
            setLoading(false);
        }
    }

    function displayFlights(flights) {
        if (!flightResults) return;
        const sortBy = sortBySelect ? sortBySelect.value : 'price';
        const sorted = [...flights].sort(function(a, b) {
            if (sortBy === 'price')     return (a.price || 0) - (b.price || 0);
            if (sortBy === 'duration')  return getDurationMinutes(a.duration) - getDurationMinutes(b.duration);
            if (sortBy === 'departure') return (a.departure_time || '').localeCompare(b.departure_time || '');
            if (sortBy === 'arrival')   return (a.arrival_time   || '').localeCompare(b.arrival_time   || '');
            return 0;
        });

        if (resultsCountSpan) resultsCountSpan.textContent = sorted.length;

        const cards = sorted.map(function(flight, index) {
            const usdPrice = flight.price || 0;
            const priceNote = flight.price_note ? ' <span style="font-size:10px;opacity:.6;">est.</span>' : '';
            const price = usdPrice > 0
                ? (typeof window.Currency !== 'undefined' ? window.Currency.format(usdPrice) : '$' + usdPrice.toLocaleString())
                : 'Price TBD';
            return '<div class="flight-card" data-flight-index="' + index + '">' +
                '<div class="flight-header">' +
                    '<div class="airline-info">' +
                        '<div class="airline-name">' + escapeHtml(flight.airline || 'Airline') + '</div>' +
                        '<div class="flight-number">Flight ' + escapeHtml(flight.flight_number || 'N/A') + '</div>' +
                    '</div>' +
                    '<div class="flight-price" data-price-usd="' + usdPrice + '">' + price + priceNote + '<span class="price-per-person">per person</span></div>' +
                '</div>' +
                '<div class="flight-body">' +
                    '<div class="flight-route">' +
                        '<div class="departure-info">' +
                            '<div class="time">' + escapeHtml(flight.departure_time || '--:--') + '</div>' +
                            '<div class="airport">' + escapeHtml(flight.departure_airport || 'Departure') + '</div>' +
                            '<div class="date">' + formatDate(flight.departure_date || (departureDateInput ? departureDateInput.value : '')) + '</div>' +
                        '</div>' +
                        '<div class="flight-duration">' +
                            '<div class="duration-line"></div>' +
                            '<div class="duration-text"><i class="fas fa-plane"></i> ' + escapeHtml(flight.duration || '--h --m') + '</div>' +
                            '<div class="stops-info">' + (flight.stops ? flight.stops + ' stop(s)' : 'Direct') + '</div>' +
                        '</div>' +
                        '<div class="arrival-info">' +
                            '<div class="time">' + escapeHtml(flight.arrival_time || '--:--') + '</div>' +
                            '<div class="airport">' + escapeHtml(flight.arrival_airport || 'Arrival') + '</div>' +
                            '<div class="date">' + formatDate(flight.arrival_date || (departureDateInput ? departureDateInput.value : '')) + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="flight-footer">' +
                        '<div class="baggage-info"><i class="fas fa-suitcase"></i> ' + (flight.baggage || '1 bag included') + '</div>' +
                        '<button class="book-flight-btn" onclick="bookFlight(' + index + ')">' +
                            '<i class="fas fa-ticket-alt"></i> Book Now' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        flightResults.innerHTML = '<div class="flights-list">' + cards + '</div>';
        if (resultsSection) resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    window.bookFlight = function (flightIndex) {
        const flight = currentFlights[flightIndex];
        if (!flight) return;
        if (!getCsrfToken()) { window.location.href = '/login'; return; }

        if (typeof Swal === 'undefined') {
            if (!confirm('Book ' + (flight.airline || 'this flight') + '?')) return;
            submitBooking(flight).then(function(d) {
                if (d && d.success) alert('Booked! Reference: ' + d.booking_reference);
            }).catch(function(e) { alert(e.message); });
            return;
        }

        const price = flight.price ? fmt(flight.price) : 'Price TBD';
        Swal.fire({
            title: 'Confirm Booking',
            html: '<div style="text-align:left;padding:8px 0;">' +
                '<div style="background:#f8f4f0;border-radius:6px;padding:14px;margin-bottom:12px;">' +
                '<strong style="color:#3b1f2b;">' + escapeHtml(flight.airline || 'Airline') + ' — ' + escapeHtml(flight.flight_number || '') + '</strong><br>' +
                '<span style="font-size:13px;color:#6b5b4f;">' + escapeHtml(flight.departure_airport || '') + ' → ' + escapeHtml(flight.arrival_airport || '') + '</span>' +
                '</div>' +
                '<div style="font-size:24px;font-weight:700;color:#3b1f2b;text-align:center;">' + price +
                '<span style="font-size:13px;font-weight:normal;color:#6b5b4f;"> per person</span></div></div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c9a96e',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: '<i class="fas fa-ticket-alt"></i> Confirm Booking',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: function() {
                return submitBooking(flight).catch(function(err) {
                    Swal.showValidationMessage(err.message);
                });
            },
        }).then(function(result) {
            if (result.isConfirmed && result.value && result.value.success) {
                Swal.fire({
                    title: 'Booking Confirmed!',
                    html: '<p>Reference: <strong>' + result.value.booking_reference + '</strong></p>',
                    icon: 'success',
                    confirmButtonColor: '#c9a96e',
                    confirmButtonText: 'View Bookings',
                    showCancelButton: true,
                    cancelButtonText: 'Stay Here',
                }).then(function(r) { if (r.isConfirmed) window.location.href = '/bookings'; });
            }
        });
    };

    async function submitBooking(flight) {
        const res = await fetch('/api/bookings/flight', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                airline:           flight.airline           || '',
                flight_number:     flight.flight_number     || '',
                departure_airport: flight.departure_airport || flight.departure_iata || '',
                arrival_airport:   flight.arrival_airport   || flight.arrival_iata   || '',
                departure_time:    flight.departure_time    || null,
                arrival_time:      flight.arrival_time      || null,
                departure_date:    flight.departure_date    || (departureDateInput ? departureDateInput.value : ''),
                duration:          flight.duration          || null,
                price:             flight.price             || 0,
                adults:            parseInt(passengersInput ? passengersInput.value : 1) || 1,
                travel_class:      classSelect ? classSelect.value.toUpperCase() : 'ECONOMY',
            }),
        });
        if (res.status === 401) { window.location.href = '/login'; throw new Error('Please log in to book.'); }
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Booking failed. Please try again.');
        return data;
    }

    function showNoResults(message) {
        if (!flightResults) return;
        flightResults.innerHTML = '<div class="no-results"><i class="fas fa-plane-slash"></i><h3>No Flights Found</h3><p>' + escapeHtml(message) + '</p></div>';
        if (resultsCountSpan) resultsCountSpan.textContent = '0';
        showResultsSection(true);
    }

    function showError(message) {
        if (!flightResults) return;
        flightResults.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i><h3>Error</h3><p>' + escapeHtml(message) + '</p></div>';
        showResultsSection(true);
    }

    function setLoading(loading) {
        if (!searchBtn) return;
        searchBtn.classList.toggle('loading', loading);
        searchBtn.disabled = loading;
    }

    function showResultsSection(show) {
        if (resultsSection) resultsSection.style.display = show ? 'block' : 'none';
    }

    window.searchFlights = searchFlights;

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('from') && urlParams.has('to')) {
        if (fromInput) fromInput.value = urlParams.get('from');
        if (toInput)   toInput.value   = urlParams.get('to');
        if (departureDateInput) departureDateInput.value = urlParams.get('departure_date') || today;
        if (returnDateInput && urlParams.has('return_date')) returnDateInput.value = urlParams.get('return_date');
        setTimeout(function() { searchFlights(); }, 500);
    }
});