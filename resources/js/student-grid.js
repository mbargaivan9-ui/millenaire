/**
 * Real-time Student Data Grid & Filters
 * Millénaire Connect
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeStudentFilters();
    initializeAttendanceUpload();
});

/**
 * Initialize Student Filters
 */
function initializeStudentFilters() {
    const filterForm = document.querySelector('form[method="GET"]');
    if (!filterForm) return;

    // Real-time search
    const searchInput = filterForm.querySelector('input[name="search"]');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    }

    // Auto-submit on filter change
    const filterSelects = filterForm.querySelectorAll('select');
    filterSelects.forEach(select => {
        select.addEventListener('change', () => filterForm.submit());
    });
}

/**
 * Initialize Attendance Bulk Upload
 */
function initializeAttendanceUpload() {
    const bulkForm = document.querySelector('form[action*="bulkCreate"]');
    if (!bulkForm) return;

    const classSelect = bulkForm.querySelector('select[name="classe_id"]');
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            loadClassStudents(this.value);
        });
    }
}

/**
 * Load Class Students for Attendance
 */
function loadClassStudents(classeId) {
    if (!classeId) return;

    fetch(`/api/admin/class/${classeId}/students`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.querySelector('#studentsList');
        if (!container) return;

        let html = '';
        data.students.forEach(student => {
            html += `
                <div class="form-check">
                    <input class="form-check-input student-checkbox" type="checkbox" 
                           name="absent_students[]" value="${student.id}" id="student_${student.id}">
                    <label class="form-check-label" for="student_${student.id}">
                        ${student.user.name} (${student.matricule})
                    </label>
                </div>
            `;
        });

        container.innerHTML = html;
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Quick Status Update (inline)
 */
function updateStudentStatus(studentId, newStatus) {
    fetch(`/api/admin/students/${studentId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Statut mis à jour');
            // Update UI element
            const statusBadge = document.querySelector(`[data-student-id="${studentId}"] .status-badge`);
            if (statusBadge) {
                statusBadge.textContent = newStatus;
                statusBadge.className = `badge bg-${getBadgeColor(newStatus)}`;
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Get Badge Color by Status
 */
function getBadgeColor(status) {
    const colors = {
        'paid': 'success',
        'partial': 'warning',
        'unpaid': 'danger',
        'active': 'success',
        'inactive': 'secondary'
    };
    return colors[status] || 'secondary';
}

/**
 * Show Success Message
 */
function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.insertBefore(alertDiv, document.body.firstChild);
    setTimeout(() => alertDiv.remove(), 5000);
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
    
    document.body.insertBefore(alertDiv, document.body.firstChild);
    setTimeout(() => alertDiv.remove(), 5000);
}

/**
 * Batch Import Students
 */
function importStudents(file) {
    const formData = new FormData();
    formData.append('file', file);

    fetch('/api/admin/students/import', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(`${data.imported} étudiants importés`);
            setTimeout(() => location.reload(), 1500);
        } else {
            showErrorMessage(data.message || 'Erreur lors de l\'import');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Erreur lors de l\'import');
    });
}

/**
 * Download Template
 */
function downloadTemplate(templateType = 'students') {
    const link = document.createElement('a');
    link.href = `/templates/${templateType}-import-template.csv`;
    link.download = `${templateType}-template.csv`;
    link.click();
}
