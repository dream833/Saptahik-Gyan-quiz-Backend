# 📱 App Connection Guide (Flutter)

> This file documents how the **app (Flutter)** connects to the backend APIs.
> Base URL: `https://saptahikgyan.space/wb-admin`

---

## 🔔 Notifications

### GET / POST Notifications List (App)

- **Endpoint:** `https://saptahikgyan.space/wb-admin/Api/app/get-notifications.php`
- **Method:** POST (JSON body — send `{}` or nothing)
- **Content-Type:** `application/json`
- **Auth:** None (broadcast to all users)

#### Request

```dart
final response = await http.post(
  Uri.parse('https://saptahikgyan.space/wb-admin/Api/app/get-notifications.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({}),
);
```

#### Response

```json
{
  "status": true,
  "total_notifications": 3,
  "data": [
    {
      "id": 5,
      "title": "New Test Added",
      "message": "A new daily mock test \"Physics Mock 03\" has been scheduled on 05 Aug 2026.",
      "type": "test",
      "created_at": "2026-08-04 12:30:00"
    },
    {
      "id": 4,
      "title": "New Solution Added",
      "message": "A new question & answer has been added: \"What is Force?\"",
      "type": "solution",
      "created_at": "2026-08-04 11:00:00"
    },
    {
      "id": 3,
      "title": "Welcome Offer",
      "message": "Flat 20% off on all courses this week!",
      "type": "custom",
      "created_at": "2026-08-03 09:15:00"
    }
  ]
}
```

#### Field meaning

| Field | Description |
|-------|-------------|
| `id` | Notification id |
| `title` | Short title shown in bold |
| `message` | Full notification text (can be empty) |
| `type` | `test` = mock test alert · `solution` = solution/PYQ alert · `custom` = manual notice |
| `created_at` | Server timestamp (format `YYYY-MM-DD HH:MM:SS`) |

#### Notes for the app

- **Sorting:** Already newest-first from the API. No client sort needed.
- **Empty state:** If `data` is `[]`, show "No notifications yet".
- **Inactive notifications** are filtered out server-side — the app never sees them.
- The app does **not** need to track read/unread — it's a simple list.

---

## 📲 Push Notifications (FCM)

Notifications also arrive as **real push notifications** on the device. The backend sends them automatically whenever a notification is created (new test, new solution, or custom notice).

### 1. Register the device token (call after login)

- **Endpoint:** `https://saptahikgyan.space/wb-admin/Api/app/register-device.php`
- **Method:** POST JSON

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

final fcm = FirebaseMessaging.instance;
final fcmToken = await fcm.getToken();

final response = await http.post(
  Uri.parse('https://saptahikgyan.space/wb-admin/Api/app/register-device.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'user_id': userId,       // logged-in user id
    'token': fcmToken,       // FCM registration token
    'platform': 'android',   // or 'ios'
  }),
);
```

**Response:** `{ "status": true, "message": "Device registered successfully" }`

### 2. Remove the token (on logout)

- **Endpoint:** `https://saptahikgyan.space/wb-admin/Api/app/unregister-device.php`
- **Body:** `{ "token": "<fcmToken>" }`

### 3. Configure Firebase in the app (use THIS config)

This is the project's **Firebase web/app config** (project `wbpathshala-app`):

```dart
// lib/firebase_options.dart (or FirebaseOptions in main.dart)
import 'package:firebase_core/firebase_core.dart';

const FirebaseOptions firebaseOptions = FirebaseOptions(
  apiKey: 'AIzaSyCOPV1r6fcM4N0di9yytZTQ0WMtAtuaalQ',
  appId: '1:509494487420:web:219d3115cad05793efdcf9',
  messagingSenderId: '509494487420',
  projectId: 'wbpathshala-app',
  authDomain: 'wbpathshala-app.firebaseapp.com',
  storageBucket: 'wbpathshala-app.firebasestorage.app',
  measurementId: 'G-TQH0EXCM95',
);
```

In `main()`:

```dart
await Firebase.initializeApp(options: firebaseOptions);
```

**Also required in the Flutter project (from Firebase Console → Project Settings → Your apps):**
- **Android:** `google-services.json` → `android/app/`
- **iOS:** `GoogleService-Info.plist` → `ios/Runner/`
- `pubspec.yaml`: add `firebase_core`, `firebase_messaging`

> ⚠️ **Important — this config is for the APP only.** The **PHP server** does NOT use it.
> The server needs the **service account key** (`google_service.json`) — Firebase Console → Project Settings → **Service accounts** → **Generate new private key** → upload to `/wb-admin/google_service.json`. Both belong to the same project `wbpathshala-app`.

The push payload the app receives is:

| Field | Value |
|-------|-------|
| `notification.title` | e.g. "New Test Added" |
| `notification.body` | Message text |
| `data.type` | `test` \| `solution` \| `custom` |
| `data.notification_id` | id to open the in-app notification list |

Foreground handler example:

```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  // show in-app banner / update notification list
  print(message.notification?.title);
  print(message.data['type']);
});
```

> **Note:** `google_service.json` (service account key) must be uploaded to the server project root for push to work. Until then, in-app notifications still work — push is just skipped.

---

## Other App APIs (reference)

| Feature | Endpoint | Notes |
|---------|----------|-------|
| Login | `Api/app/login.php` | POST JSON |
| Signup | `Api/app/signup.php` | POST JSON |
| Profile (get) | `Api/app/fetch-profile.php` | POST JSON (user_id) |
| Profile (update) | `Api/app/update-profile.php` | **JSON + base64 image** (not multipart!) |
| Classes | `Api/app/get-class.php` | POST JSON |
| Subjects | `Api/app/get-subject.php` | POST JSON (class_id) |
| Daily tests | `Api/app/daily-test/tests.php` | Only **today's** tests |
| Daily classes | `Api/app/daily-test/classes.php` | Only classes with a test **today** |
| Daily subjects | `Api/app/daily-test/subjects.php` | Only subjects with a test **today** |
| Start set | `Api/app/all-tests/start-set.php` | POST JSON (set_id) |
| Sets list | `Api/app/all-tests/sets.php` | POST JSON (chapter_id) |
| Mock test list | `Api/app/get-mocktest.php` | POST JSON (class_id, subject_id) |
| Start mock test | `Api/app/start-mocktest.php` | POST JSON |
| Submit mock test | `Api/app/submit-mocktest.php` | POST JSON |
| Check answer | `Api/app/check-mock-answer.php` | POST JSON |
| Dashboard | `Api/app/dashboard.php` | POST JSON (user_id) |
| Solution question types | `Api/app/get-solution-question-type.php` | POST JSON |
| Register device (FCM) | `Api/app/register-device.php` | POST JSON (user_id, token, platform) |
| Unregister device (FCM) | `Api/app/unregister-device.php` | POST JSON (token) |

---

## 🧪 Admin-side test tools

(For the admin panel — no app changes needed.)

### Browser push in the admin panel

The admin panel itself uses **Firebase Web Messaging** — the admin's browser registers as a device (`platform: 'web'`) and receives pushes. The web config + service worker (`firebase-messaging-sw.js`) are already in the admin project. No app changes needed for this.

The admin panel's **VAPID key** is already set in `Admin/js/firebase-config.js` → `FIREBASE_VAPID_KEY`. The only remaining server-side piece is `google_service.json` (service account key) at the project root — that's what lets the server actually send.

| Endpoint | Purpose |
|----------|---------|
| `Api/admin/fcm-status.php` | POST JSON `{}` → shows if the server key is configured + device count |
| `Api/admin/test-push.php` | POST JSON `{ "title", "message", "type" }` → sends a test push to **all devices** without saving a notification |

The admin **Notifications** page shows a live FCM status card + a **Send Test Push** button. Use it to verify a device receives pushes before trusting the app UI.
