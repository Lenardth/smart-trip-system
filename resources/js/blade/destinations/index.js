(function () {
    const path = window.location.pathname;
    
    // Check if we're on a show page (e.g., /destinations/123)
    if (path.match(/^\/destinations\/\d+/)) {
        // Load show page module
        import('./show.js').catch(err => console.error('[destinations] show module failed:', err));
    } else {
        // Index page functionality
        if (typeof window.filterDest !== 'function') {
            window.filterDest = function (filter, el) {
                document.querySelectorAll('.cont-tab').forEach(function (t) { t.classList.remove('active'); });
                if (el) el.classList.add('active');
            };
        }
    }
})();