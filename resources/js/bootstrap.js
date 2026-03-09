import axios from 'axios';
window.axios = axios;

// Configure Axios to include X-Requested-With header (identifies AJAX requests)
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * CRITICAL: Configure Axios to include CSRF Token from meta tag
 * This prevents 419 Unauthenticated errors on POST/PUT/DELETE requests
 * Laravel validates the X-CSRF-TOKEN header against session tokens
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    window.csrfToken = csrfToken;
    console.log('[Axios] CSRF Token configured successfully');
} else {
    console.error('[Axios] ERROR: CSRF token meta tag not found. Add <meta name="csrf-token" content="{{ csrf_token() }}"> to your layout!');
}
