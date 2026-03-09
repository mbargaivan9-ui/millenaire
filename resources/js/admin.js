/**
 * Admin Panel - Form Handlers & Validations
 * Millénaire Connect
 */

// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Student form - Real-time email validation
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validateEmail(this.value);
        });
    }

    // Matricule - Real-time uniqueness check
    const matriculeInput = document.querySelector('input[name="matricule"]');
    if (matriculeInput) {
        matriculeInput.addEventListener('blur', function() {
            checkMatriculUniqueness(this.value);
        });
    }

    // Time validation (end_time > start_time)
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');
    
    if (startTimeInput && endTimeInput) {
        endTimeInput.addEventListener('change', function() {
            validateTimeRange(startTimeInput.value, this.value);
        });
    }

    // Amount formatting
    const amountInputs = document.querySelectorAll('input[name="amount"]');
    amountInputs.forEach(input => {
        input.addEventListener('blur', function() {
            this.value = formatCurrency(this.value);
        });
    });

    // Table select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActionButton();
        });

        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkActionButton);
        });
    }
});

/**
 * Email Validation
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = regex.test(email);
    
    const input = document.querySelector('input[name="email"]');
    if (input) {
        input.classList.remove('is-invalid');
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
    }
    
    return isValid;
}

/**
 * Matricule Uniqueness Check
 */
function checkMatriculUniqueness(matricule) {
    if (!matricule) return;

    fetch(`/api/admin/check-matricule?matricule=${encodeURIComponent(matricule)}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const input = document.querySelector('input[name="matricule"]');
        if (data.exists) {
            input.classList.add('is-invalid');
            showErrorMessage('Ce matricule existe déjà');
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Time Range Validation
 */
function validateTimeRange(startTime, endTime) {
    const start = new Date(`2000-01-01 ${startTime}`);
    const end = new Date(`2000-01-01 ${endTime}`);

    const endInput = document.querySelector('input[name="end_time"]');
    
    if (end <= start) {
        endInput.classList.add('is-invalid');
        showErrorMessage('L\'heure de fin doit être après l\'heure de début');
    } else {
        endInput.classList.remove('is-invalid');
        endInput.classList.add('is-valid');
    }
}

/**
 * Format Currency
 */
function formatCurrency(value) {
    const numValue = parseInt(value.replace(/\D/g, ''));
    if (!numValue) return '';
    return numValue.toLocaleString('fr-FR');
}

/**
 * Show Error Message
 */
function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-circle me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const form = document.querySelector('form');
    if (form) {
        form.parentElement.insertBefore(alertDiv, form);
    }
}

/**
 * Update Bulk Action Button
 */
function updateBulkActionButton() {
    const checked = document.querySelectorAll('.student-checkbox:checked').length;
    const btn = document.querySelector('[data-bs-target="#bulkModal"]');
    if (btn) {
        btn.disabled = checked === 0;
    }
}

/**
 * Bulk Actions Handler
 */
document.addEventListener('DOMContentLoaded', function() {
    const bulkForm = document.querySelector('form[action*="bulkUpdate"]');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const selectedIds = Array.from(document.querySelectorAll('.student-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                e.preventDefault();
                showErrorMessage('Sélectionnez au moins un étudiant');
                return false;
            }

            document.getElementById('selectedIds').value = JSON.stringify(selectedIds);
        });
    }
});

/**
 * Data Table Enhancement
 */
document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        // Add row hover effects
        table.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(79, 70, 229, 0.05)';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
});

/**
 * Export Confirmation
 */
function confirmExport() {
    return confirm('Êtes-vous sûr de vouloir exporter ces données?');
}

/**
 * Delete Confirmation
 */
function confirmDelete() {
    return confirm('Cette action est irréversible. Êtes-vous sûr?');
}

/**
 * Print Report
 */
function printReport() {
    window.print();
}
