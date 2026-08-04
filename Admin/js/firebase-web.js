/**
 * Admin Panel — Firebase Web Messaging bootstrap.
 *
 * Loads after firebase-config.js. Initializes Firebase, asks for
 * notification permission, registers this browser as a device
 * (Api/admin/register-web-device.php), and shows pushes while the
 * admin panel is open in the foreground.
 */
(function () {
    // Only run in a secure context with notification support
    if (!('Notification' in window) || !('serviceWorker' in navigator)) {
        return;
    }

    function showAdminToast(message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        }
    }

    async function loadFirebaseSDK() {
        // Load firebase-app + firebase-messaging compat builds from CDN
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.onload = resolve;
                s.onerror = () => reject(new Error('Failed to load ' + src));
                document.head.appendChild(s);
            });
        }

        await loadScript('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
        await loadScript('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');
    }

    async function initBrowserPush() {
        if (!window.firebase) {
            try {
                await loadFirebaseSDK();
            } catch (e) {
                console.warn('[FCM] Firebase SDK load failed:', e);
                return;
            }
        }

        // VAPID key not configured -> skip registration (documented in config file)
        if (!FIREBASE_VAPID_KEY) {
            console.warn('[FCM] VAPID key not set in Admin/js/firebase-config.js — browser push disabled.');
            reportStatus({ registered: false, reason: 'no-vapid-key' });
            return;
        }

        try {
            const app = window.firebase.initializeApp(FIREBASE_WEB_CONFIG, 'admin');
            const messaging = window.firebase.messaging(app);

            // Register the service worker first
            const registration = await navigator.serviceWorker.register(FIREBASE_SW_PATH, { scope: '/wb-admin/' });

            // Ask permission (triggered by user gesture ideally, but firebase handles the flow)
            let permission = Notification.permission;
            if (permission === 'default') {
                permission = await Notification.requestPermission();
            }

            if (permission !== 'granted') {
                console.warn('[FCM] Notification permission denied.');
                reportStatus({ registered: false, reason: 'permission-denied' });
                return;
            }

            // Get the browser FCM token (use the 'admin' app messaging instance)
            const token = await messaging.getToken({
                vapidKey: FIREBASE_VAPID_KEY,
                serviceWorkerRegistration: registration
            });

            if (token) {
                // Register with the backend
                const API_BASE = (function () {
                    const parts = window.location.pathname.split('/');
                    return window.location.origin + parts.slice(0, parts.length - 2).join('/') + '/Api/admin/';
                })();

                await fetch(API_BASE + 'register-web-device.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: token, platform: 'web' })
                });

                reportStatus({ registered: true, token: token });
            }

            // Foreground messages -> show browser notification + toast
            messaging.onMessage((payload) => {
                const title = payload.notification?.title || 'New Notification';
                const body = payload.notification?.body || '';
                if (Notification.permission === 'granted' && navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({ type: 'show-notification', title, body });
                }
                // Fallback: in-page toast
                showAdminToast(title + (body ? ' — ' + body : ''), 'info');
            });

        } catch (e) {
            console.warn('[FCM] Browser push init error:', e);
            reportStatus({ registered: false, reason: 'error', message: String(e.message || e) });
        }
    }

    /**
     * Report browser-push status both via callback AND global state,
     * so pages that define FCMWebStatusCallback AFTER this script runs
     * (script include order) can still read the result.
     */
    function reportStatus(status) {
        window.FCMWebStatus = status;
        if (typeof window.FCMWebStatusCallback === 'function') {
            window.FCMWebStatusCallback(status);
        }
    }

    // Start after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBrowserPush);
    } else {
        initBrowserPush();
    }
})();
