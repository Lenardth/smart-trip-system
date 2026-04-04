document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.getElementById('flightSearchForm');
    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');
    const departureDateInput = document.getElementById('departure_date');
    const returnDateInput = document.getElementById('return_date');
    const passengersInput = document.getElementById('passengers');
    const classSelect = document.getElementById('class');
    const resultsSection = document.getElementById('resultsSection');
    const flightResults = document.getElementById('flightResults');
    const resultsCountSpan = document.getElementById('resultsCount');
    const sortBySelect = document.getElementById('sortBy');
    const searchBtn = document.querySelector('.search-btn');

    let currentFlights = [];

    
    const today = new Date().toISOString().split('T')[0];
    departureDateInput.min = today;
    if (returnDateInput) {
        returnDateInput.min = today;
    }

    
    const tripTabs = document.querySelectorAll('.trip-type-tab');
    let currentTripType = 'round-trip';

    if (tripTabs.length > 0) {
        tripTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tripTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentTripType = this.dataset.type;

                
                const returnDateGroup = document.getElementById('returnDateGroup');
                if (currentTripType === 'one-way') {
                    if (returnDateGroup) returnDateGroup.style.display = 'none';
                } else {
                    if (returnDateGroup) returnDateGroup.style.display = 'block';
                }
            });
        });
    }

    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            await searchFlights();
        });
    }

    
    if (sortBySelect) {
        sortBySelect.addEventListener('change', function() {
            if (currentFlights.length > 0) {
                displayFlights(currentFlights);
            }
        });
    }

    
    window.fillRoute = function(from, to) {
        if (fromInput) fromInput.value = from;
        if (toInput) toInput.value = to;
        
        setTimeout(() => searchFlights(), 100);
    };

    
    async function searchFlights() {
        
        if (!fromInput.value.trim()) {
            showError('Please enter departure city or airport');
            fromInput.focus();
            return;
        }

        if (!toInput.value.trim()) {
            showError('Please enter arrival city or airport');
            toInput.focus();
            return;
        }

        if (!departureDateInput.value) {
            showError('Please select departure date');
            departureDateInput.focus();
            return;
        }

        
        const requestData = {
            from: fromInput.value.trim(),
            to: toInput.value.trim(),
            departure_date: departureDateInput.value,
            adults: parseInt(passengersInput.value) || 1,
            travel_class: classSelect.value.toUpperCase()
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
                body: JSON.stringify(requestData)
            });

            const result = await response.json();

            if (result.success) {
                if (result.flights && result.flights.length > 0) {
                    currentFlights = result.flights;
                    displayFlights(currentFlights);
                    showResultsSection(true);
                } else {
                    showNoResults(result.message || 'No flights found for this route and date. Try different dates or destinations.');
                }
            } else {
                showError(result.message || 'Failed to search flights. Please try again.');
            }
        } catch (error) {
            console.error('Search error:', error);
            showError('Network error. Please check your connection and try again.');
        } finally {
            setLoading(false);
        }
    }

    
    function displayFlights(flights) {
        if (!flightResults) return;

        
        const sortBy = sortBySelect ? sortBySelect.value : 'price';
        const sortedFlights = [...flights].sort((a, b) => {
            switch(sortBy) {
                case 'price':
                    return (a.price || 0) - (b.price || 0);
                case 'duration':
                    return getDurationMinutes(a.duration) - getDurationMinutes(b.duration);
                case 'departure':
                    return (a.departure_time || '').localeCompare(b.departure_time || '');
                case 'arrival':
                    return (a.arrival_time || '').localeCompare(b.arrival_time || '');
                default:
                    return 0;
            }
        });

        
        if (resultsCountSpan) {
            resultsCountSpan.textContent = sortedFlights.length;
        }

        
        let html = '<div class="flights-list">';

        sortedFlights.forEach((flight, index) => {
            html += `
                <div class="flight-card" data-flight-index="${index}">
                    <div class="flight-header">
                        <div class="airline-info">
                            <div class="airline-name">${escapeHtml(flight.airline || 'Airline')}</div>
                            <div class="flight-number">Flight ${escapeHtml(flight.flight_number || 'N/A')}</div>
                        </div>
                        <div class="flight-price">
                            $${flight.price ? flight.price.toLocaleString() : 'N/A'}
                            <span class="price-per-person">per person</span>
                        </div>
                    </div>

                    <div class="flight-body">
                        <div class="flight-route">
                            <div class="departure-info">
                                <div class="time">${escapeHtml(flight.departure_time || '--:--')}</div>
                                <div class="airport">${escapeHtml(flight.departure_airport || flight.origin || 'Departure')}</div>
                                <div class="date">${formatDate(flight.departure_date || departureDateInput.value)}</div>
                            </div>

                            <div class="flight-duration">
                                <div class="duration-line"></div>
                                <div class="duration-text">
                                    <i class="fas fa-plane"></i>
                                    ${escapeHtml(flight.duration || '--h --m')}
                                </div>
                                <div class="stops-info">${flight.stops ? flight.stops + ' stop(s)' : 'Direct'}</div>
                            </div>

                            <div class="arrival-info">
                                <div class="time">${escapeHtml(flight.arrival_time || '--:--')}</div>
                                <div class="airport">${escapeHtml(flight.arrival_airport || flight.destination || 'Arrival')}</div>
                                <div class="date">${formatDate(flight.arrival_date || departureDateInput.value)}</div>
                            </div>
                        </div>

                        <div class="flight-footer">
                            <div class="baggage-info">
                                <i class="fas fa-suitcase"></i> ${flight.baggage || '1 bag included'}
                            </div>
                            <button class="book-flight-btn" onclick="bookFlight(${index})">
                                <i class="fas fa-ticket-alt"></i> Book Now
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        flightResults.innerHTML = html;

        
        if (resultsSection) {
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    
    function getDurationMinutes(duration) {
        if (!duration) return 999999;
        const match = duration.match(/(\d+)\s*h(?:\s*(\d+)\s*m)?/i);
        if (match) {
            const hours = parseInt(match[1]) || 0;
            const minutes = parseInt(match[2]) || 0;
            return hours * 60 + minutes;
        }
        return 999999;
    }

    
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    
    function showNoResults(message) {
        if (!flightResults) return;
        flightResults.innerHTML = `
            <div class="no-results">
                <i class="fas fa-plane-slash"></i>
                <h3>No Flights Found</h3>
                <p>${escapeHtml(message)}</p>
                <button class="try-again-btn" onclick="document.getElementById('flightSearchForm').reset()">
                    <i class="fas fa-redo"></i> Reset Search
                </button>
            </div>
        `;
        if (resultsCountSpan) resultsCountSpan.textContent = '0';
        showResultsSection(true);
    }

    
    function showError(message) {
        if (!flightResults) return;
        flightResults.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error</h3>
                <p>${escapeHtml(message)}</p>
                <button class="try-again-btn" onclick="searchFlights()">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            </div>
        `;
        showResultsSection(true);
    }

    
    function setLoading(loading) {
        if (!searchBtn) return;
        if (loading) {
            searchBtn.classList.add('loading');
            searchBtn.disabled = true;
        } else {
            searchBtn.classList.remove('loading');
            searchBtn.disabled = false;
        }
    }

    
    function showResultsSection(show) {
        if (resultsSection) {
            resultsSection.style.display = show ? 'block' : 'none';
        }
    }

    
    function getCsrfToken() {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) {
            return tokenMeta.content;
        }
        return document.querySelector('input[name="_token"]')?.value || '';
    }

    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    
    window.bookFlight = function(flightIndex) {
        const flight = currentFlights[flightIndex];
        if (flight) {
            
            alert(`Booking flight: ${flight.airline} - Flight ${flight.flight_number}\nPrice: $${flight.price}\n\nBooking functionality will be implemented soon.`);
            
            
        }
    };

    
    window.searchFlights = searchFlights;

    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('from') && urlParams.has('to')) {
        fromInput.value = urlParams.get('from');
        toInput.value = urlParams.get('to');
        departureDateInput.value = urlParams.get('departure_date') || today;
        if (returnDateInput && urlParams.has('return_date')) {
            returnDateInput.value = urlParams.get('return_date');
        }
        setTimeout(() => searchFlights(), 500);
    }
});
