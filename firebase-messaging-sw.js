// ============================================================
// Firebase Cloud Messaging - Web Service Worker
// Located at the project root so its scope covers the whole site.
// This file MUST be served from: /wb-admin/firebase-messaging-sw.js
// (Do NOT move it into a subfolder - the service worker scope
//  needs to cover the admin panel pages.)
// ============================================================

importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCOPV1r6fcM4N0di9yytZTQ0WMtAtuaalQ",
    authDomain: "wbpathshala-app.firebaseapp.com",
    projectId: "wbpathshala-app",
    storageBucket: "wbpathshala-app.firebasestorage.app",
    messagingSenderId: "509494487420",
    appId: "1:509494487420:web:219d3115cad05793efdcf9",
    measurementId: "G-TQH0EXCM95"
});

const messaging = firebase.messaging();

// Handle background messages (app/tab not in foreground)
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Background message ', payload);
    const notificationTitle = payload.notification?.title || 'New Notification';
    const notificationOptions = {
        body: payload.notification?.body || '',
        data: payload.data || {}
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle 'show-notification' requests from pages (foreground display)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'show-notification') {
        self.registration.showNotification(event.data.title || 'New Notification', {
            body: event.data.body || '',
            data: event.data.data || {}
        });
    }
});

// Click on notification -> open admin notifications page
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.focus();
                    return;
                }
            }
            if (clients.openWindow) {
                return clients.openWindow('/wb-admin/Admin/notifications.php');
            }
        })
    );
});
