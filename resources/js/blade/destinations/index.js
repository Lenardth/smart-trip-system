// Destinations index page — loaded by app.js for /destinations
// The actual grid rendering is done inline in the blade via @push('scripts')
// This file handles any additional interactivity needed on the page.

(function () {
    // Expose filterDest globally in case it wasn't set by the inline script
    if (typeof window.filterDest !== 'function') {
        window.filterDest = function (filter, el) {
            document.querySelectorAll('.cont-tab').forEach(function (t) { t.classList.remove('active'); });
            if (el) el.classList.add('active');
        };
    }
})();
