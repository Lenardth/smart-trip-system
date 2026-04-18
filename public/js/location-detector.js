/**
 * Location & Airport Detection Module
 * Privacy-protected location detection with opt-in consent
 */

(function() {
    'use strict';

    class LocationDetector {
        constructor() {
            this.hasConsent = false;
            this.currentLocation = null;
            this.departureAirport = null;
            this.airports = [];
            this.init();
        }

        async init() {
            // Check if user has previously given consent
            this.hasConsent = localStorage.getItem('location_consent') === 'true';
            
            // Load saved departure airport
            await this.loadDepartureAirport();
            
            // Load airports list
            await this.loadAirports();
            
            // Initialize UI
            this.initUI();
        }

        async loadDepartureAirport() {
            try {
                const response = await fetch('/api/location/departure-airport');
                const data = await response.json();
                
                if (data.success && data.airport.code) {
                    this.departureAirport = data.airport;
                    this.updateDepartureDisplay();
                }
            } catch (error) {
                console.warn('Failed to load departure airport:', error);
            }
        }

        async loadAirports() {
            try {
                const response = await fetch('/api/location/airports');
                const data = await response.json();
                
                if (data.success) {
                    this.airports = data.airports;
                }
            } catch (error) {
                console.warn('Failed to load airports:', error);
            }
        }

        initUI() {
            // Add location selector to navigation or relevant pages
            this.addLocationSelector();
        }

        addLocationSelector() {
            // Silent mode - no visible selector
            // Just load departure airport and use it for auto-fill
            if (this.departureAirport) {
                this.populateFlightForm();
            }
        }

        showAirportModal() {
            // Create modal
            const modal = document.createElement('div');
            modal.id = 'airport-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeIn 0.2s ease;
            `;
            
            modal.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 12px;
                    padding: 24px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 80vh;
                    overflow-y: auto;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                ">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; color: var(--deep); font-size: 20px;">
                            <i class="fas fa-plane-departure" style="color: var(--gold); margin-right: 8px;"></i>
                            Select Departure Airport
                        </h3>
                        <button id="close-modal-btn" style="
                            background: transparent;
                            border: none;
                            font-size: 24px;
                            cursor: pointer;
                            color: var(--text-muted);
                            padding: 0;
                            width: 32px;
                            height: 32px;
                        ">&times;</button>
                    </div>
                    
                    <div style="margin-bottom: 20px; padding: 16px; background: var(--gold-dim); border-radius: 8px; border: 1px solid var(--gold);">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                            <i class="fas fa-shield-alt" style="color: var(--gold);"></i>
                            <strong style="color: var(--deep);">Privacy Protected</strong>
                        </div>
                        <p style="margin: 0; font-size: 13px; color: var(--deep); line-height: 1.5;">
                            We can detect your nearest airport automatically, but this requires your consent. 
                            Your location data is never stored and only used to suggest airports.
                        </p>
                        <button id="auto-detect-btn" style="
                            margin-top: 12px;
                            background: var(--gold);
                            border: none;
                            color: var(--deep);
                            padding: 10px 16px;
                            border-radius: 6px;
                            cursor: pointer;
                            font-size: 13px;
                            font-weight: 600;
                            width: 100%;
                            transition: opacity 0.2s;
                        ">
                            <i class="fas fa-location-arrow"></i> Auto-Detect My Location
                        </button>
                    </div>
                    
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--deep); font-size: 13px;">
                            Or choose manually:
                        </label>
                        <input type="text" id="airport-search" placeholder="Search airports..." style="
                            width: 100%;
                            padding: 10px 14px;
                            border: 1.5px solid var(--border);
                            border-radius: 8px;
                            font-size: 14px;
                            outline: none;
                            transition: border-color 0.2s;
                        ">
                    </div>
                    
                    <div id="airports-list" style="
                        max-height: 300px;
                        overflow-y: auto;
                        border: 1px solid var(--border);
                        border-radius: 8px;
                    "></div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Populate airports list
            this.populateAirportsList();
            
            // Add event listeners
            document.getElementById('close-modal-btn')?.addEventListener('click', () => {
                modal.remove();
            });
            
            document.getElementById('auto-detect-btn')?.addEventListener('click', () => {
                this.autoDetectLocation();
            });
            
            document.getElementById('airport-search')?.addEventListener('input', (e) => {
                this.filterAirports(e.target.value);
            });
            
            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }

        populateAirportsList(filter = '') {
            const listElement = document.getElementById('airports-list');
            if (!listElement) return;
            
            const filtered = filter 
                ? this.airports.filter(a => 
                    a.name.toLowerCase().includes(filter.toLowerCase()) ||
                    a.code.toLowerCase().includes(filter.toLowerCase()) ||
                    a.city.toLowerCase().includes(filter.toLowerCase()) ||
                    a.country.toLowerCase().includes(filter.toLowerCase())
                  )
                : this.airports;
            
            if (filtered.length === 0) {
                listElement.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-muted);">No airports found</div>';
                return;
            }
            
            listElement.innerHTML = filtered.map(airport => `
                <div class="airport-item" data-code="${airport.code}" data-name="${airport.name}" data-city="${airport.city}" style="
                    padding: 12px 16px;
                    border-bottom: 1px solid var(--border);
                    cursor: pointer;
                    transition: background 0.2s;
                ">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; color: var(--deep); margin-bottom: 4px;">
                                ${airport.name} (${airport.code})
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                ${airport.city}, ${airport.country}
                            </div>
                        </div>
                        <i class="fas fa-chevron-right" style="color: var(--text-muted);"></i>
                    </div>
                </div>
            `).join('');
            
            // Add click handlers
            document.querySelectorAll('.airport-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.selectAirport({
                        code: item.dataset.code,
                        name: item.dataset.name,
                        city: item.dataset.city,
                    });
                });
                
                item.addEventListener('mouseenter', (e) => {
                    e.currentTarget.style.background = 'var(--gold-dim)';
                });
                
                item.addEventListener('mouseleave', (e) => {
                    e.currentTarget.style.background = 'transparent';
                });
            });
        }

        filterAirports(query) {
            this.populateAirportsList(query);
        }

        async autoDetectLocation() {
            const btn = document.getElementById('auto-detect-btn');
            if (!btn) return;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Detecting...';
            
            try {
                const response = await fetch('/api/location/detect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ consent: true })
                });
                
                const data = await response.json();
                
                if (data.success && data.nearest_airport) {
                    // Save consent
                    localStorage.setItem('location_consent', 'true');
                    this.hasConsent = true;
                    
                    // Select the detected airport
                    this.selectAirport({
                        code: data.nearest_airport.code,
                        name: data.nearest_airport.name,
                        city: data.nearest_airport.city,
                    });
                    
                    this.showNotification(`Detected: ${data.nearest_airport.name}`, 'success');
                } else {
                    this.showNotification('Could not detect location. Please select manually.', 'info');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-arrow"></i> Auto-Detect My Location';
                }
            } catch (error) {
                console.error('Auto-detect failed:', error);
                this.showNotification('Detection failed. Please select manually.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-location-arrow"></i> Auto-Detect My Location';
            }
        }

        async selectAirport(airport) {
            try {
                const response = await fetch('/api/location/departure-airport', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        airport_code: airport.code,
                        airport_name: airport.name,
                        city: airport.city,
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.departureAirport = airport;
                    this.updateDepartureDisplay();
                    this.showNotification(`Departure airport set to ${airport.name}`, 'success');
                    
                    // Close modal
                    document.getElementById('airport-modal')?.remove();
                    
                    // Auto-populate flight form if present
                    this.populateFlightForm();
                }
            } catch (error) {
                console.error('Failed to save airport:', error);
                this.showNotification('Failed to save airport', 'error');
            }
        }

        updateDepartureDisplay() {
            const display = document.getElementById('departure-display');
            if (display && this.departureAirport) {
                display.textContent = `${this.departureAirport.name} (${this.departureAirport.code})`;
            }
        }

        populateFlightForm() {
            if (!this.departureAirport) return;
            
            // Wait a bit to ensure page is loaded
            setTimeout(() => {
                // Find flight form "from" field
                const fromInput = document.querySelector('#from') ||
                                 document.querySelector('input[name="from"]') ||
                                 document.querySelector('#flightFrom');
                
                if (fromInput && !fromInput.value) {
                    fromInput.value = this.departureAirport.code;
                    fromInput.style.opacity = '0.7'; // Show it's a suggestion
                    
                    // Clear opacity when user focuses
                    fromInput.addEventListener('focus', function() {
                        this.style.opacity = '1';
                    }, { once: true });
                    
                    fromInput.dispatchEvent(new Event('input', { bubbles: true }));
                    fromInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                // Also populate origin field on plan-trip page
                const originInput = document.querySelector('#origin') ||
                                   document.querySelector('input[name="origin"]');
                
                if (originInput && !originInput.value) {
                    originInput.value = this.departureAirport.city || this.departureAirport.name;
                    originInput.style.opacity = '0.7'; // Show it's a suggestion
                    
                    // Clear opacity when user focuses
                    originInput.addEventListener('focus', function() {
                        this.style.opacity = '1';
                    }, { once: true });
                }
            }, 600);
        }

        showNotification(message, type = 'info') {
            // Silent mode - only log to console
            console.log(`[Location] ${message}`);
        }
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        #airport-search:focus {
            border-color: var(--gold) !important;
            box-shadow: 0 0 0 3px rgba(201,169,110,0.15);
        }
    `;
    document.head.appendChild(style);

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.locationDetector = new LocationDetector();
        });
    } else {
        window.locationDetector = new LocationDetector();
    }

})();
