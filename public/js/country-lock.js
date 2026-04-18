/**
 * Country Lock Module (Silent Mode)
 * Handles persistent country selection across pages WITHOUT visible indicators
 * Works silently in the background to pre-fill suggestions
 */

(function() {
    'use strict';

    class CountryLock {
        constructor() {
            this.lockedCountry = null;
            this.lockedDestination = null;
            this.isLocked = false;
            this.init();
        }

        async init() {
            // Load current lock status from server
            await this.loadLockStatus();
            
            // Auto-populate fields if country is locked (silently)
            if (this.isLocked) {
                this.applyLockedCountry();
            }
            
            // Listen for country selection events
            this.attachEventListeners();
        }

        async loadLockStatus() {
            try {
                const response = await fetch('/api/country-lock');
                const data = await response.json();
                
                if (data.locked) {
                    this.isLocked = true;
                    this.lockedCountry = data.country;
                    this.lockedDestination = data.destination || '';
                }
            } catch (error) {
                console.warn('[Country Lock] Failed to load status:', error);
            }
        }

        async lockCountry(country, destination = '') {
            try {
                const response = await fetch('/api/country-lock/lock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ country, destination })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.isLocked = true;
                    this.lockedCountry = country;
                    this.lockedDestination = destination;
                    console.log('[Country Lock] Locked:', country);
                    return true;
                }
            } catch (error) {
                console.error('[Country Lock] Failed to lock:', error);
            }
            return false;
        }

        async unlockCountry() {
            try {
                const response = await fetch('/api/country-lock/unlock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.isLocked = false;
                    this.lockedCountry = null;
                    this.lockedDestination = null;
                    console.log('[Country Lock] Unlocked');
                    return true;
                }
            } catch (error) {
                console.error('[Country Lock] Failed to unlock:', error);
            }
            return false;
        }

        applyLockedCountry() {
            if (!this.isLocked || !this.lockedCountry) return;
            
            const currentPage = window.location.pathname;
            
            // Apply to plan-trip page
            if (currentPage.includes('/plan-trip')) {
                this.applyToPlanTrip();
            }
            
            // Apply to flights page
            if (currentPage.includes('/flights')) {
                this.applyToFlights();
            }
            
            // Apply to accommodations/stays page
            if (currentPage.includes('/accommodations')) {
                this.applyToAccommodations();
            }
            
            // Apply to discover page filters
            if (currentPage.includes('/discover')) {
                this.applyToDiscover();
            }
        }

        applyToPlanTrip() {
            // Wait for page to be ready
            setTimeout(() => {
                // If there's a destination search field, populate it as a suggestion
                const destinationInput = document.querySelector('input[name="destination"]') 
                    || document.querySelector('#destination');
                
                if (destinationInput && !destinationInput.value) {
                    destinationInput.value = this.lockedDestination || this.lockedCountry;
                    destinationInput.style.opacity = '0.7'; // Show it's a suggestion
                    
                    // Clear opacity when user focuses
                    destinationInput.addEventListener('focus', function() {
                        this.style.opacity = '1';
                    }, { once: true });
                }
                
                // Store in global variable for the trip planner
                if (window.lastPayload) {
                    window.lastPayload.country = this.lockedCountry;
                    window.lastPayload.destination = this.lockedDestination || this.lockedCountry;
                }
            }, 500);
        }

        applyToFlights() {
            // Wait for page to be ready
            setTimeout(() => {
                // Auto-fill "To" field with locked destination
                const toInput = document.querySelector('#to') || 
                    document.querySelector('input[name="to"]') ||
                    document.querySelector('#flightTo');
                
                if (toInput && !toInput.value) {
                    toInput.value = this.lockedDestination || this.lockedCountry;
                    toInput.style.opacity = '0.7'; // Show it's a suggestion
                    
                    // Clear opacity when user focuses
                    toInput.addEventListener('focus', function() {
                        this.style.opacity = '1';
                    }, { once: true });
                    
                    // Trigger change event
                    toInput.dispatchEvent(new Event('input', { bubbles: true }));
                    toInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                // Also check if we have a saved departure airport and fill "From" field
                this.checkAndFillDepartureAirport();
            }, 800); // Increased delay to ensure page is fully loaded
        }
        
        checkAndFillDepartureAirport() {
            // Check if location detector has a saved airport
            if (window.locationDetector && window.locationDetector.departureAirport) {
                const fromInput = document.querySelector('#from') ||
                    document.querySelector('input[name="from"]') ||
                    document.querySelector('#flightFrom');
                
                if (fromInput && !fromInput.value) {
                    const airport = window.locationDetector.departureAirport;
                    fromInput.value = airport.code || airport.city || airport.name;
                    fromInput.style.opacity = '0.7';
                    
                    fromInput.addEventListener('focus', function() {
                        this.style.opacity = '1';
                    }, { once: true });
                    
                    fromInput.dispatchEvent(new Event('input', { bubbles: true }));
                    fromInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }

        applyToAccommodations() {
            // Wait for page to be ready
            setTimeout(() => {
                const searchInput = document.querySelector('#searchInput') ||
                    document.querySelector('input[name="q"]') || 
                    document.querySelector('input[name="search"]') ||
                    document.querySelector('#accommodationSearch');
                
                if (searchInput && !searchInput.value) {
                    searchInput.value = this.lockedDestination || this.lockedCountry;
                    searchInput.style.opacity = '0.7'; // Show it's a suggestion
                    
                    // Clear opacity when user focuses
                    searchInput.addEventListener('focus', function() {
                        this.style.opacity = '1';
                    }, { once: true });
                    
                    // Trigger search events
                    searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                    searchInput.dispatchEvent(new Event('change', { bubbles: true }));
                    searchInput.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true }));
                    
                    // Trigger search after a short delay to ensure handlers are ready
                    setTimeout(() => {
                        // If there's a search button, click it
                        const searchBtn = document.querySelector('#searchBtn') ||
                            document.querySelector('button[type="submit"]') || 
                            document.querySelector('.search-btn');
                        if (searchBtn) {
                            searchBtn.click();
                        }
                    }, 400);
                }
            }, 800); // Increased delay to ensure page is fully loaded
        }

        applyToDiscover() {
            // Store for discover page filters (silently)
            if (window.discoverFilters) {
                window.discoverFilters.country = this.lockedCountry;
            }
        }

        attachEventListeners() {
            // Listen for destination card clicks
            document.addEventListener('click', (e) => {
                const destCard = e.target.closest('[data-destination-country]');
                if (destCard) {
                    const country = destCard.getAttribute('data-destination-country');
                    const destination = destCard.getAttribute('data-destination-name') || '';
                    
                    if (country) {
                        this.lockCountry(country, destination);
                    }
                }
            });
            
            // Listen for "Plan This Trip" button clicks
            document.addEventListener('click', (e) => {
                const planBtn = e.target.closest('a[href*="plan-trip"]');
                if (planBtn) {
                    const url = new URL(planBtn.href, window.location.origin);
                    const destination = url.searchParams.get('destination');
                    const country = url.searchParams.get('country');
                    
                    if (country) {
                        this.lockCountry(country, destination || '');
                    }
                }
            });
        }

        // Public API
        getLockedCountry() {
            return this.isLocked ? {
                country: this.lockedCountry,
                destination: this.lockedDestination
            } : null;
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.countryLock = new CountryLock();
        });
    } else {
        window.countryLock = new CountryLock();
    }

    // Expose lockThisCountry globally for onclick handlers (but works silently)
    window.lockThisCountry = function(country, destination) {
        if (window.countryLock) {
            window.countryLock.lockCountry(country, destination);
        }
    };

})();
