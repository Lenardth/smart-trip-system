(function () {
    var btn     = document.getElementById('mobHamburger');
    var drawer  = document.getElementById('mobDrawer');
    var overlay = document.getElementById('mobOverlay');
    var closeBtn= document.getElementById('mobDrawerClose');

    function openDrawer() {
        drawer.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        if (btn) btn.classList.add('open');
    }
    function closeDrawer() {
        drawer.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
        if (btn) btn.classList.remove('open');
    }

    if (btn)      btn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay)  overlay.addEventListener('click', closeDrawer);
    if (drawer)   drawer.querySelectorAll('.mob-link').forEach(function(l) {
        l.addEventListener('click', closeDrawer);
    });
})();
