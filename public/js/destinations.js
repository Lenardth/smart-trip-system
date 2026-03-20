document.querySelectorAll('.cont-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.cont-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});

