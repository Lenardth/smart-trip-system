/**
 * Country Lock Module
 * Handles persistent country selection across discover, destinations, plan-trip, flights, and stays pages
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
            
            // Initialize UI
            this.initUI();
            
            // Auto-populate fields if country is locked
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
                console.warn('Failed to load country lock status:', error);
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
                    this.updateUI();
                    this.showNotification(`Country locked: ${country}`, 'success');
                    return true;
                }
            } catch (error) {
                console.error('Failed to lock country:', error);
                this.showNotification('Failed to lock country', 'error');
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
                    this.updateUI();
                    this.showNotification('Country unlocked', 'success');
                    return true;
                }
            } catch (error) {
                console.error('Failed to unlock country:', error);
                this.showNotification('Failed to unlock country', 'error');
            }
            return false;
        }

        initUI() {
            // Add lock indicator to navigation
            this.addLockIndicator();
            
            // Update UI based on current lock status
            this.updateUI();
        }

        addLockIndicator() {
            // Check if indicator already exists
            if (document.getElementById('country-lock-indicator')) return;
            
            // Create lock indicator element
            const indicator = document.createElement('div');
            indicator.id = 'country-lock-indicator';
            indicator.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: var(--gold, #c9a96e);
                color: var(--deep, #3b1f2b);
                padding: 10px 16px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                font-size: 13px;
                font-weight: 600;
                z-index: 9999;
                display: none;
                align-items: center;
                gap: 10px;
                transition: all 0.3s ease;
            `;
            
            indicator.innerHTML = `
                <i class="fas fa-lock"></i>
                <span id="country-lock-text"></span>
                <button id="country-unlock-btn" style="
                    background: transparent;
                    border: none;
                    color: var(--deep, #3b1f2b);
                    cursor: pointer;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    transition: background 0.2s;
                " title="Unlock country">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(indicator);
            
            // Add unlock button handler
            const unlockBtn = document.getElementById('country-unlock-btn');
            if (unlockBtn) {
                unlockBtn.addEventListener('click', () => {
                    this.unlockCountry();
                });
            }
        }

        updateUI() {
            const indicator = document.getElementById('country-lock-indicator');
            const lockText = document.getElementById('country-lock-text');
            
            if (!indicator || !lockText) return;
            
            if (this.isLocked && this.lockedCountry) {
                indicator.style.display = 'flex';
                lockText.textContent = this.lockedDestination 
                    ? `${this.lockedDestination}, ${this.lockedCountry}`
                    : this.lockedCountry;
            } else {
                indicator.style.display = 'none';
            }
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
                // If there's a destination search field, populate it
                const destinationInput = document.querySelector('input[name="destination"]') 
                    || document.querySelector('#destination');
                
                if (destinationInput && !destinationInput.value) {
                    destinationInput.value = this.lockedDestination || this.lockedCountry;
                }
                
                // Store in hidden field or global variable for the trip planner
                if (window.lastPayload) {
                    window.lastPayload.country = this.lockedCountry;
                    window.lastPayload.destination = this.lockedDestination || this.lockedCountry;
                }
            }, 500);
        }

        applyToFlights() {
            // Wait for page to be ready
            setTimeout(() => {
                const toInput = document.querySelector('input[name="to"]') 
                    || document.querySelector('#to')
                    || document.querySelector('#flightTo');
                
                if (toInput && !toInput.value) {
                    toInput.value = this.lockedDestination || this.lockedCountry;
                    
                    // Trigger change event
                    toInput.dispatchEvent(new Event('input', { bubbles: true }));
                    toInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }, 500);
        }

        applyToAccommodations() {
            // Wait for page to be ready
            setTimeout(() => {
                const searchInput = document.querySelector('input[name="q"]') 
                    || document.querySelector('input[name="search"]')
                    || document.querySelector('#accommodationSearch');
                
                if (searchInput && !searchInput.value) {
                    searchInput.value = this.lockedDestination || this.lockedCountry;
                    
                    // Trigger search
                    searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    // If there's a search button, click it
                    const searchBtn = document.querySelector('button[type="submit"]') 
                        || document.querySelector('.search-btn');
                    if (searchBtn) {
                        setTimeout(() => searchBtn.click(), 300);
                    }
                }
            }, 500);
        }

        applyToDiscover() {
            // Store for discover page filters
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

        showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                bottom: 30px;
                right: 30px;
                background: ${type === 'success' ? '#3a7d44' : type === 'error' ? '#c0392b' : 'var(--deep)'};
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                font-size: 14px;
                z-index: 10000;
                animation: slideInUp 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutDown 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Public API
        getLockedCountry() {
            return this.isLocked ? {
                country: this.lockedCountry,
                destination: this.lockedDestination
            } : null;
        }
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutDown {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(100px);
                opacity: 0;
            }
        }
        
        #country-unlock-btn:hover {
            background: rgba(59, 31, 43, 0.1) !important;
        }
    `;
    document.head.appendChild(style);

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.countryLock = new CountryLock();
        });
    } else {
        window.countryLock = new CountryLock();
    }

    // Expose lockThisCountry globally for onclick handlers
    window.lockThisCountry = function(country, destination) {
        if (window.countryLock) {
            window.countryLock.lockCountry(country, destination);
        }
    };

})();

