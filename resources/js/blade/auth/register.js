function toggleAgencyFields() {
            const agencyFields = document.getElementById('agency-fields');
            const isAgency = document.getElementById('type_agency').checked;
            const agencyNameInput = document.getElementById('agency_name');

            if (isAgency) {
                agencyFields.classList.add('show');
                // Remove the required attribute - let Laravel handle validation
                // agencyNameInput.removeAttribute('required');
            } else {
                agencyFields.classList.remove('show');
                // Clear the agency_name field when not needed
                agencyNameInput.value = '';
                // agencyNameInput.removeAttribute('required');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleAgencyFields();

            // Form validation before submission
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const isAgency = document.getElementById('type_agency').checked;
                const agencyNameInput = document.getElementById('agency_name');

                if (isAgency && !agencyNameInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter your agency name.');
                    agencyNameInput.focus();
                    return false;
                }

                return true;
            });
        });

        // Expose for Blade inline onchange="toggleAgencyFields()"
        window.toggleAgencyFields = toggleAgencyFields;
