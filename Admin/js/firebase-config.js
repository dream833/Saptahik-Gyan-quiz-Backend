/**
 * Firebase Web Config — used by the admin panel (browser push notifications).
 * Keep this file in sync with firebase-messaging-sw.js at the project root.
 */
const FIREBASE_WEB_CONFIG = {
    apiKey: "AIzaSyCOPV1r6fcM4N0di9yytZTQ0WMtAtuaalQ",
    authDomain: "wbpathshala-app.firebaseapp.com",
    projectId: "wbpathshala-app",
    storageBucket: "wbpathshala-app.firebasestorage.app",
    messagingSenderId: "509494487420",
    appId: "1:509494487420:web:219d3115cad05793efdcf9",
    measurementId: "G-TQH0EXCM95"
};

/**
 * VAPID key (Web Push certificate) — REQUIRED for browser push.
 * Get it: Firebase Console → Project Settings → Cloud Messaging tab →
 *         Web Push certificates → Key pair.
 * Leave empty ("") to disable browser push (admin panel still works).
 */
const FIREBASE_VAPID_KEY = "BBg3CsRULgMQWEaaFVX0Wv7NauLZilbfMM48Of5YcuSWdb5yXyKoulaFmyfWETdK8MdS25FxC9argqhFjJgux7k";

// Service worker path — must match the file at the project root
const FIREBASE_SW_PATH = "/wb-admin/firebase-messaging-sw.js";
