// ============================================
// ATTENDANCE MANAGEMENT
// ============================================

class AttendanceManager {
    constructor() {
        this.form = document.querySelector('[data-attendance-form]');
        this.bulkForm = document.querySelector('[data-bulk-attendance-form]');
        this.init();
    }

    init() {
        if (this.form) {
            this.form.addEventListener('change', (e) => this.handleStatusChange(e));
        }

        if (this.bulkForm) {
            document.querySelectorAll('[data-attendance-row]').forEach(row => {
                row.addEventListener('change', () => this.updateBulkStatus(row));
            });
        }
    }

    handleStatusChange(e) {
        if (e.target.name === 'status') {
            const reason = document.querySelector('[name="reason"]');
            
            if (e.target.value === 'absent' || e.target.value === 'justified') {
                reason?.classList.remove('d-none');
            } else {
                reason?.classList.add('d-none');
            }
        }
    }

    updateBulkStatus(row) {
        const status = row.querySelector('[name*="status"]')?.value;
        const reason = row.querySelector('[name*="reason"]')?.style;
        
        if (status === 'absent' || status === 'justified') {
            reason.display = '';
        } else {
            reason.display = 'none';
        }
    }

    async submitBulk() {
        if (confirm('Êtes-vous sûr de vouloir enregistrer l\'assiduité?')) {
            this.bulkForm.submit();
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    new AttendanceManager();
});
