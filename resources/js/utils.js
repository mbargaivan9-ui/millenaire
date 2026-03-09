/**
 * Utilities & Helper Functions
 * Millénaire Connect Admin Panel
 */

/**
 * Debounce Function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format Date
 */
function formatDate(date, format = 'DD/MM/YYYY') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    const formats = {
        'DD/MM/YYYY': `${day}/${month}/${year}`,
        'YYYY-MM-DD': `${year}-${month}-${day}`,
        'DD MMMM YYYY': `${day} ${getMonthName(d.getMonth())} ${year}`
    };

    return formats[format] || formats['DD/MM/YYYY'];
}

/**
 * Get Month Name
 */
function getMonthName(monthIndex) {
    const months = [
        'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'
    ];
    return months[monthIndex];
}

/**
 * Format Currency
 */
function formatCurrency(amount, currency = 'FCFA') {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0
    }).format(amount).replace('€', currency);
}

/**
 * Format Phone Number
 */
function formatPhoneNumber(phone) {
    const digits = phone.replace(/\D/g, '');
    if (digits.length === 9) {
        return `+237 ${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6)}`;
    }
    return phone;
}

/**
 * Validate Form
 */
function validateForm(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

/**
 * Copy to Clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copié dans le presse-papiers', 'success');
    }).catch(() => {
        showNotification('Erreur lors de la copie', 'error');
    });
}

/**
 * Show Notification
 */
function showNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const icon = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass[type] || 'alert-info'} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas ${icon[type] || 'fa-info-circle'} me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.insertBefore(alert, document.body.firstChild);
    setTimeout(() => alert.remove(), 5000);
}

/**
 * Get Query Parameter
 */
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

/**
 * Reload Page
 */
function reloadPage(delay = 0) {
    setTimeout(() => {
        window.location.reload();
    }, delay);
}

/**
 * AJAX GET Request
 */
function ajaxGet(url, callback) {
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => callback(data))
    .catch(error => console.error('Error:', error));
}

/**
 * AJAX POST Request
 */
function ajaxPost(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => callback(data))
    .catch(error => console.error('Error:', error));
}

/**
 * Loading State
 */
function setLoading(element, isLoading = true) {
    if (isLoading) {
        element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Chargement...';
        element.disabled = true;
    } else {
        element.innerHTML = element.dataset.originalText || 'Soumettre';
        element.disabled = false;
    }
}

/**
 * Confirm Dialog
 */
function showConfirmDialog(message) {
    return new Promise((resolve) => {
        if (confirm(message)) {
            resolve(true);
        } else {
            resolve(false);
        }
    });
}

/**
 * Parse JSON Safely
 */
function safeJsonParse(jsonString) {
    try {
        return JSON.parse(jsonString);
    } catch (e) {
        console.error('JSON Parse Error:', e);
        return null;
    }
}

/**
 * Highlight Table Row
 */
function highlightRow(rowElement, color = 'yellow', duration = 2000) {
    const original = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = color;
    setTimeout(() => {
        rowElement.style.backgroundColor = original;
    }, duration);
}

/**
 * Scroll to Element
 */
function scrollToElement(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
