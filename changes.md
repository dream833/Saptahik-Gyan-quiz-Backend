# 📝 All Changes & Fixes

---

## 🔔 Notification Feature

**Date:** Aug 4, 2026

### New Feature Overview

A notification system has been added:

1. **Admin Panel** — New `Notifications` page (`Admin/notifications.php`) where admins can:
   - Create **custom notifications** (title + message + type)
   - View all notifications (auto + manual)
   - **Toggle active/inactive** (inactive = hidden from app)
   - **Edit** and **Delete** notifications

2. **Auto Notifications** — When content is added, a notification is created automatically:
   - New Daily Mock Test → "New Test Added"
   - New Set (All Mock Test) → "New Mock Test Set"
   - New Solution Question → "New Solution Added"
   - New Suggestion → "New Suggestion"
   - New Previous Year Question → "New Previous Year Question"

3. **App Side** — New endpoint `Api/app/get-notifications.php` returns active notifications for the app (broadcast to all users, newest first).

### 🗄️ SQL To Run On Server (phpMyAdmin → SQL tab)

**Run BOTH tables below:**

```sql
-- 1) In-app notifications
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('test','solution','custom') DEFAULT 'custom',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- 2) FCM device tokens (push notifications)
CREATE TABLE `device_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `platform` enum('android','ios') DEFAULT 'android',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `device_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `device_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```

### 📁 New Files Created

| File | Purpose |
|------|---------|
| `firebase-messaging-sw.js` | **FCM web service worker** (project root — browser push) |
| `Admin/js/firebase-config.js` | Firebase web config + **VAPID key** placeholder |
| `Admin/js/firebase-web.js` | Browser push bootstrap: init, permission, token register, onMessage |
| `Api/admin/register-web-device.php` | Register the **admin browser** as a device (platform `web`) |
| `Admin/notifications.php` | Admin page to manage notifications |
| `Api/admin/add-notification.php` | Create manual notification (JSON: title, message, type) |
| `Api/admin/get-notifications.php` | List all notifications (admin) |
| `Api/admin/update-notification.php` | Edit notification / toggle is_active |
| `Api/admin/delete-notification.php` | Delete notification |
| `Api/admin/fcm-status.php` | **FCM status** — key present, project, device count (powers the status card) |
| `Api/admin/test-push.php` | **Send test push** to all devices without saving a notification |
| `Api/app/get-notifications.php` | **App endpoint** — active notifications only |
| `Api/app/register-device.php` | **App endpoint** — register FCM device token |
| `Api/app/unregister-device.php` | **App endpoint** — remove FCM device token (logout) |
| `utils/notification_helper.php` | Reusable `create_notification()` helper |
| `utils/fcm_helper.php` | **FCM push sender** (JWT + OAuth2 + FCM v1, pure PHP) |
| `.htaccess` | Blocks web access to `google_service.json` |

### ✏️ Modified Files

| File | Change |
|------|--------|
| `quiz.sql` | Added `notifications` + `device_tokens` tables |
| `utils/notification_helper.php` | Now also sends FCM push after each insert |
| `Admin/Dashboard.php` | Sidebar: added Notifications link |
| `Admin/Users.php` | Sidebar: added Notifications link |
| `Admin/dailymocktest.php` | Sidebar: added Notifications link |
| `Admin/allmocktestscreen.php` | Sidebar: added Notifications link |
| `Admin/solutions.php` | Sidebar: added Notifications link |
| `Admin/dailymockresult.php` | Sidebar: added Notifications link |
| `Admin/allmocktestresult.php` | Sidebar: added Notifications link |
| `Api/admin/add-mocktest.php` | Auto-notify when test added |
| `Api/admin/add-set.php` | Auto-notify when set added |
| `Api/admin/add-solution-questions.php` | Auto-notify when solution question added |
| `Api/admin/add-solution-suggestion.php` | Auto-notify when suggestion added |
| `Api/admin/add-previous-year-question.php` | Auto-notify when PYQ added |

### 🔄 Admin APIs

- **POST** `Api/admin/add-notification.php`
  - Body: `{ "title": "...", "message": "...", "type": "custom|test|solution" }`
  - Response: `{ "status": true, "notification_id": 1 }`

- **POST** `Api/admin/get-notifications.php` (body can be empty `{}`)
  - Response: `{ "status": true, "data": [ { id, title, message, type, is_active, created_at } ] }`

- **POST** `Api/admin/update-notification.php`
  - Full edit: `{ "notification_id": 1, "title": "...", "message": "...", "type": "test" }`
  - Toggle: `{ "notification_id": 1, "is_active": 0 }` (0=hide, 1=show)

- **POST** `Api/admin/delete-notification.php`
  - Body: `{ "notification_id": 1 }`

### 📱 App API

- **POST** `Api/app/get-notifications.php` (body can be empty `{}`)
  - Response:
    ```json
    {
      "status": true,
      "total_notifications": 2,
      "data": [
        {
          "id": 1,
          "title": "New Test Added",
          "message": "A new daily mock test ...",
          "type": "test",
          "created_at": "2026-08-04 10:00:00"
        }
      ]
    }
    ```
  - `type` field: `test` (tests) | `solution` (solutions/PYQ) | `custom` (manual)
  - Only `is_active = 1` notifications are returned, newest first.

### 🔔 FCM Push Setup (google_service.json)

**Firebase project:** `wbpathshala-app` (messagingSenderId `509494487420`)

1. **Firebase Console** → Project Settings → **Service accounts** → **Generate new private key**
2. Rename the downloaded key to **`google_service.json`**
3. **Upload it to the server** at: `/wb-admin/google_service.json`
   (next to `quiz.sql`, at the **project root** — NOT in `Api/` or `utils/`)
4. The new `.htaccess` blocks direct web access to it (`403` if someone tries `https://saptahikgyan.space/wb-admin/google_service.json`)
5. Push sending is **automatic** — every notification (manual + auto) also pushes to all registered devices. If the key file is missing, the backend just skips push (in-app notifications still work).

**⚠️ Two different Firebase files — don't mix them up:**

| File | Used by | Purpose |
|------|---------|---------|
| `google_service.json` (service account key) | **PHP server** (`utils/fcm_helper.php`) | Lets the server **send** pushes (FCM HTTP v1) |
| Web config (`apiKey`, `messagingSenderId`…) | **Flutter app** (`firebase_options.dart`) | Lets the app **receive** pushes |

Both belong to the same project `wbpathshala-app`. Full Flutter setup with the exact config is in **App.md** (section "📲 Push Notifications").

### 📱 App Side (Flutter) — register the device token

After the user logs in, the app must register its FCM token once:

```dart
final response = await http.post(
  Uri.parse('https://saptahikgyan.space/wb-admin/Api/app/register-device.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'user_id': userId,        // logged-in user id
    'token': fcmToken,        // from firebase_messaging: getToken()
    'platform': 'android',    // or 'ios'
  }),
);
// { "status": true, "message": "Device registered successfully" }
```

Call `unregister-device.php` (body: `{ "token": "..." }`) on logout.

Push payload the app receives:
- `notification.title` / `notification.body`
- `data.type` = `test` | `solution` | `custom`
- `data.notification_id` = id to deep-link into the in-app list

### 🖥️ Browser Push in the Admin Panel (new)

The admin panel now connects to Firebase **in the browser** (uses the web config: project `wbpathshala-app`).

**How it works:**
1. `firebase-web.js` loads on all admin pages → asks for notification permission → gets the browser FCM token → registers it via `register-web-device.php`
2. The **Notifications** page shows a badge: "● This browser: registered"
3. When any push is sent, the admin browser receives it (notification in the browser + toast)
4. `firebase-messaging-sw.js` (project **root**) handles background pushes + click → opens Notifications page

**✅ VAPID key — SET** (Aug 4, 2026): `FIREBASE_VAPID_KEY` is filled in `Admin/js/firebase-config.js`. Browser push can now register.

**Remaining to go live:** upload `google_service.json` (service account key) to the project root so the **server** can send pushes. Without it, the server can't send, so nothing reaches the browser either.

**Upload these too:**
- `firebase-messaging-sw.js` → `/wb-admin/firebase-messaging-sw.js` (project root!)
- `Admin/js/firebase-config.js`, `Admin/js/firebase-web.js` → `wb-admin/Admin/js/`
- `Api/admin/register-web-device.php` → `wb-admin/Api/admin/`

**SQL (if table already exists):**
```sql
ALTER TABLE `device_tokens`
  MODIFY `platform` enum('android','ios','web') DEFAULT 'android',
  MODIFY `user_id` int(11) NOT NULL DEFAULT 0;
```

### 🔍 Admin FCM status card + Test Push (new)

The **Notifications** page now shows an **FCM status card** at the top:
- **Green** = `google_service.json` present → server can push
- **Red** = key missing → shows exactly what to upload
- Shows the Firebase **project id** + how many **devices are registered** (phones + admin browsers)
- **"Send Test Push"** button → opens a small modal, sends a test push to all devices **without saving a notification** (great for verifying FCM end-to-end)
- **"● This browser: registered"** badge → confirms your admin browser is connected

Admin APIs:
- **POST** `Api/admin/fcm-status.php` → `{ status, data: { configured, project_id, client_email, total_devices } }`
- **POST** `Api/admin/test-push.php` (body: `{ title, message, type }`) → sends push, returns `{ status, data: { sent, failed, detail } }`
- **POST** `Api/admin/register-web-device.php` (body: `{ token, platform: 'web' }`) → registers the admin browser

### 🚀 Upload Checklist (to saptahikgyan.space)

1. Run the SQL above (both tables) in phpMyAdmin
2. Upload all new + modified files
3. Upload `google_service.json` to the project root
4. Open Admin → Notifications → the status card turns green when the key is detected
5. Click **Send Test Push** → devices should receive it

---

## Earlier Fixes (kept for history)

*(Previous fixes related to allmocktestscreen, dailymocktest, profile update etc. were documented in prior sessions.)*
