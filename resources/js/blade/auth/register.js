function toggleAgencyFields() {
            const agencyFields = document.getElementById('agency-fields');
            const isAgency = document.getElementById('type_agency').checked;
            const agencyNameInput = document.getElementById('agency_name');

            if (isAgency) {
                agencyFields.classList.add('show');
                
                
            } else {
                agencyFields.classList.remove('show');
                
                agencyNameInput.value = '';
                
            }
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            toggleAgencyFields();

            
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

        
        window.toggleAgencyFields = toggleAgencyFields;