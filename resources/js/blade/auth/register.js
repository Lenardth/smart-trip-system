function toggleAgencyFields() {
    const agencyFields = document.getElementById('agency-fields');
    const agencyType = document.getElementById('type_agency');
    const agencyNameInput = document.getElementById('agency_name');

    if (!agencyFields || !agencyType || !agencyNameInput) return;

    if (agencyType.checked) {
        agencyFields.classList.add('show');
        return;
    }

    agencyFields.classList.remove('show');
    agencyNameInput.value = '';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleAgencyFields();

    document.querySelectorAll('input[name="user_type"]').forEach(function(input) {
        input.addEventListener('change', toggleAgencyFields);
    });

    const registerForm = document.getElementById('registerForm');
    registerForm?.addEventListener('submit', function(e) {
        const agencyType = document.getElementById('type_agency');
        const agencyNameInput = document.getElementById('agency_name');

        if (agencyType?.checked && !agencyNameInput?.value.trim()) {
            e.preventDefault();
            alert('Please enter your agency name.');
            agencyNameInput?.focus();
        }
    });
});
