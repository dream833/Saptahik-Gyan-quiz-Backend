/**
 * WB Admin API Client
 * 
 * Shared JavaScript client for connecting Admin UI pages
 * to the backend API endpoints in /Api/admin/
 * 
 * Usage:
 *   import { api } from './js/api.js';
 *   const result = await api.post('admin_login.php', { email, password });
 *   const data = await api.get('get-mocktest-status.php', { class_id: 1, subject_id: 1 });
 */

const API_BASE = (function() {
    // Auto-detect base URL from current page location
    const pathParts = window.location.pathname.split('/');
    // Go up from Admin/ to project root
    const base = window.location.origin + pathParts.slice(0, pathParts.length - 2).join('/') + '/Api/admin/';
    return base;
})();

/**
 * Generic post request to the admin API.
 * @param {string} endpoint - The API file name (e.g. 'admin_login.php')
 * @param {object} data - JSON body payload
 * @returns {Promise<object>} Parsed JSON response
 */
async function post(endpoint, data = {}) {
    try {
        const response = await fetch(API_BASE + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return await response.json();
    } catch (err) {
        return { status: false, message: 'Network error: ' + err.message };
    }
}

/**
 * Generic get request to the admin API (query params).
 * @param {string} endpoint - The API file name (e.g. 'get-mocktest-status.php')
 * @param {object} params - Query parameters as key/value pairs
 * @returns {Promise<object>} Parsed JSON response
 */
async function get(endpoint, params = {}) {
    try {
        const qs = Object.keys(params).length
            ? '?' + Object.entries(params).map(([k, v]) => encodeURIComponent(k) + '=' + encodeURIComponent(v)).join('&')
            : '';
        const response = await fetch(API_BASE + endpoint + qs, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });
        return await response.json();
    } catch (err) {
        return { status: false, message: 'Network error: ' + err.message };
    }
}

/**
 * Delete request (uses POST with JSON body, as per existing API convention).
 * @param {string} endpoint - The API file name (e.g. 'delete-class.php')
 * @param {object} data - JSON body payload with id(s)
 * @returns {Promise<object>} Parsed JSON response
 */
async function del(endpoint, data = {}) {
    return post(endpoint, data);
}

/**
 * Update request (uses POST with JSON body).
 * @param {string} endpoint - The API file name (e.g. 'update-class.php')
 * @param {object} data - JSON body payload with updated fields
 * @returns {Promise<object>} Parsed JSON response
 */
async function update(endpoint, data = {}) {
    return post(endpoint, data);
}

/**
 * Show a toast notification on the page.
 * @param {string} message - The message text
 * @param {'success'|'error'|'info'} type - Notification type
 */
function showToast(message, type = 'info') {
    // Remove existing toast
    const existing = document.querySelector('.api-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'api-toast';
    toast.style.cssText = `
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        padding: 14px 20px; border-radius: 12px; font-size: 14px;
        font-weight: 500; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12); max-width: 380px;
        animation: toastIn 0.3s ease; display: flex; align-items: center; gap: 10px;
        color: #fff;
    `;
    if (type === 'success') {
        toast.style.background = '#059669';
        toast.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>' + message;
    } else if (type === 'error') {
        toast.style.background = '#dc2626';
        toast.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>' + message;
    } else {
        toast.style.background = '#4f46e5';
        toast.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>' + message;
    }
    document.body.appendChild(toast);

    // Add animation keyframes if not already present
    if (!document.getElementById('apiToastStyle')) {
        const style = document.createElement('style');
        style.id = 'apiToastStyle';
        style.textContent = `
            @keyframes toastIn { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
            .api-toast { transition: opacity 0.3s, transform 0.3s; }
        `;
        document.head.appendChild(style);
    }

    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }
    }, 3000);
}

// Export the API client
const api = { post, get, del, update, showToast };
