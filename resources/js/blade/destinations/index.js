(function () {
    
    if (typeof window.filterDest !== 'function') {
        window.filterDest = function (filter, el) {
            document.querySelectorAll('.cont-tab').forEach(function (t) { t.classList.remove('active'); });
            if (el) el.classList.add('active');
        };
    }
})();