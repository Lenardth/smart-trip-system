// Settings Page

function showTab(name, el) {
    document.querySelectorAll('.settings-tab').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
    const tab = document.getElementById('tab-' + name);
    if (tab) tab.style.display = 'block';
    if (el) el.classList.add('active');
    return false;
}

window.confirmDelete = function () {
    if (typeof Swal === 'undefined') {
        const pw = prompt('Enter your password to confirm account deletion:');
        if (!pw) return;
        document.getElementById('deletePassword').value = pw;
        document.getElementById('deleteAccountForm').submit();
        return;
    }
    Swal.fire({
        title: 'Delete Account?',
        html: '<p style="color:#6b5b4f;margin-bottom:12px;">This cannot be undone. Enter your password to confirm.</p>' +
              '<input type="password" id="swalPw" class="swal2-input" placeholder="Your password">',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#6b5b4f',
        confirmButtonText: 'Yes, delete my account',
        preConfirm: () => {
            const pw = document.getElementById('swalPw').value;
            if (!pw) { Swal.showValidationMessage('Password is required'); return false; }
            return pw;
        }
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('deletePassword').value = result.value;
            document.getElementById('deleteAccountForm').submit();
        }
    });
};

// Auto-show tab from hash
(function () {
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        const el = document.querySelector('.settings-nav-item[href="#' + hash + '"]');
        if (el) showTab(hash, el);
    }
})();

window.showTab = showTab;
