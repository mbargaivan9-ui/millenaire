/**
 * Fee Management & Payment Tracking
 * Millénaire Connect
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeFeeFilters();
    initializeAmountFormatting();
    initializeFeeAssignment();
});

/**
 * Initialize Fee Filters
 */
function initializeFeeFilters() {
    const searchInput = document.querySelector('input[name="search"]');
    const statusSelect = document.querySelector('select[name="status"]');
    const filterForm = document.querySelector('form');

    if (searchInput) {
        let timeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', () => filterForm.submit());
    }
}

/**
 * Initialize Amount Formatting
 */
function initializeAmountFormatting() {
    const amountInputs = document.querySelectorAll('input[name="amount"]');
    
    amountInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value) {
                this.value = parseInt(value).toLocaleString('fr-FR');
            }
        });

        input.addEventListener('blur', function() {
            const value = this.value.replace(/\D/g, '');
            this.value = value ? parseInt(value).toLocaleString('fr-FR') : '';
        });
    });
}

/**
 * Initialize Fee Assignment
 */
function initializeFeeAssignment() {
    const assignForm = document.querySelector('form[action*="assignToClass"]');
    if (!assignForm) return;

    const feeSelect = assignForm.querySelector('select[name="fee_id"]');
    const classeSelect = assignForm.querySelector('select[name="classe_id"]');

    if (feeSelect && classeSelect) {
        feeSelect.addEventListener('change', updateAssignmentPreview);
        classeSelect.addEventListener('change', updateAssignmentPreview);
    }
}

/**
 * Update Assignment Preview
 */
function updateAssignmentPreview() {
    const feeId = document.querySelector('select[name="fee_id"]').value;
    const classeId = document.querySelector('select[name="classe_id"]').value;

    if (!feeId || !classeId) return;

    fetch(`/api/admin/fees/preview-assignment?fee_id=${feeId}&classe_id=${classeId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const preview = document.querySelector('#assignmentPreview');
        if (preview && data.preview) {
            preview.innerHTML = data.preview;
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Calculate Total Fee
 */
function calculateTotalFee() {
    const feeTable = document.querySelector('.fee-table');
    if (!feeTable) return;

    let total = 0;
    feeTable.querySelectorAll('tr').forEach(row => {
        const amountCell = row.querySelector('td:nth-child(2)');
        if (amountCell) {
            const amount = parseInt(amountCell.textContent.replace(/\D/g, '')) || 0;
            total += amount;
        }
    });

    const totalCell = document.querySelector('#totalFee');
    if (totalCell) {
        totalCell.textContent = total.toLocaleString('fr-FR') + ' FCFA';
    }
}

/**
 * Payment Status Indicator
 */
function getPaymentStatusBadge(status) {
    const statusMap = {
        'pending': '<span class="badge bg-warning">En attente</span>',
        'partial': '<span class="badge bg-info">Partiel</span>',
        'completed': '<span class="badge bg-success">Payé</span>',
        'overdue': '<span class="badge bg-danger">En retard</span>'
    };
    return statusMap[status] || '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Send Reminder Notifications
 */
function sendFeeReminder(studentId) {
    fetch(`/api/admin/fees/send-reminder/${studentId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Rappel envoyé à ' + data.contact);
        } else {
            showErrorMessage(data.message || 'Erreur lors de l\'envoi');
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Generate Payment Receipt
 */
function generateReceipt(paymentId) {
    const link = document.createElement('a');
    link.href = `/api/admin/fees/receipt/${paymentId}`;
    link.target = '_blank';
    link.click();
}

/**
 * Export Fee Report
 */
function exportFeeReport(format = 'pdf') {
    const startDate = document.querySelector('input[name="start_date"]')?.value || '';
    const endDate = document.querySelector('input[name="end_date"]')?.value || '';

    window.location.href = `/admin/fees/report?format=${format}&start_date=${startDate}&end_date=${endDate}`;
}

/**
 * Show Success Message
 */
function showSuccessMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.innerHTML = `
        <i class="fas fa-check-circle me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.insertBefore(alert, document.body.firstChild);
    setTimeout(() => alert.remove(), 5000);
}

/**
 * Show Error Message
 */
function showErrorMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        <i class="fas fa-exclamation-circle me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.insertBefore(alert, document.body.firstChild);
    setTimeout(() => alert.remove(), 5000);
}
