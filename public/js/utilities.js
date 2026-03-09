// ============================================
// MILLÉNAIRE CONNECT - UTILITIES
// ============================================

// ============ UTILITY FUNCTIONS ============

/**
 * Show a toast notification
 */
function showToast(message, type = 'info', duration = 5000) {
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.role = 'alert';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('[data-toast-container]') || document.body;
    container.appendChild(toast);
    
    setTimeout(() => {
        const element = document.getElementById(toastId);
        if (element) {
            element.remove();
        }
    }, duration);
}

/**
 * Format number as currency
 */
function formatCurrency(amount, currency = 'EUR') {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Format date to French format
 */
function formatDate(date, locale = 'fr-FR') {
    return new Date(date).toLocaleDateString(locale);
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate phone number
 */
function isValidPhone(phone) {
    const regex = /^\d{8,15}$/;
    return regex.test(phone.replace(/\D/g, ''));
}

// ============ AJAX REQUESTS ============

async function apiRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        }
    };

    try {
        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        showToast('Erreur: ' + error.message, 'danger');
        console.error('API Error:', error);
        return null;
    }
}

async function apiGet(url) {
    return apiRequest(url, { method: 'GET' });
}

async function apiPost(url, data) {
    return apiRequest(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

async function apiPut(url, data) {
    return apiRequest(url, {
        method: 'PUT',
        body: JSON.stringify(data)
    });
}

// ============ TABLE UTILITIES ============

/**
 * Filter table by search term
 */
function filterTable(tableSelector, searchTerm, columnIndex = 0) {
    const table = document.querySelector(tableSelector);
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cell = row.querySelectorAll('td')[columnIndex];
        const text = cell ? cell.textContent.toLowerCase() : '';
        
        row.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
    });
}

/**
 * Sort table by column  
 */
function sortTable(tableSelector, columnIndex, ascending = true) {
    const table = document.querySelector(tableSelector);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.querySelectorAll('td')[columnIndex].textContent;
        const bValue = b.querySelectorAll('td')[columnIndex].textContent;
        
        if (isNaN(aValue) && isNaN(bValue)) {
            return ascending ? 
                aValue.localeCompare(bValue) : 
                bValue.localeCompare(aValue);
        }
        
        return ascending ? 
            parseFloat(aValue) - parseFloat(bValue) : 
            parseFloat(bValue) - parseFloat(aValue);
    });
    
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableSelector, filename = 'export.csv') {
    const table = document.querySelector(tableSelector);
    if (!table) return;
    
    const csv = [];
    
    // Add headers
    const headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.textContent.trim());
    csv.push(headers.join(','));
    
    // Add rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = Array.from(tr.querySelectorAll('td'))
            .map(td => `"${td.textContent.trim().replace(/"/g, '""')}"`)
            .join(',');
        csv.push(row);
    });
    
    // Download
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// ============ INITIALIZATION ============

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Initialize Bootstrap popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });
});
