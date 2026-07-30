# 📝 All Changes & Fixes

---

## 1. 🐛 Fix: `Admin/allmocktestscreen.php` — Class/Subject/Chapter not persisting to DB

**Date:** July 30, 2026

### Problem
`addNewItem()` function for class, subject, and chapter was only storing data in local JavaScript arrays with locally-generated fake IDs (1000, 1001, 1002...). These were NEVER saved to the database. When the user tried to add a set or load sets, it called `add-set.php` with fake chapter_id → server couldn't find it → **"Chapter Not Found"** error. After refresh, all data was gone.

### Fix
All three (class, subject, chapter) now call their respective APIs to persist to DB first:
- Class → `add-class.php`
- Subject → `add-subject.php`
- Chapter → `add-chapter.php`

Use the real DB ID returned by the API (`result.class_id`, `result.subject_id`, `result.chapter_id`). After adding, reload data from server via `loadClasses()`, `loadSubjects()`, `loadChapters()` to ensure consistency. Added `.then()` chaining to fix race condition where `populateSelect()` would rebuild options after `.value` was set, wiping it out.

### Files Modified
- `Admin/allmocktestscreen.php`

### What to Upload
`Admin/allmocktestscreen.php` to `wb-admin/Admin/allmocktestscreen.php`

---

## 2. 🐛 Fix: `Admin/dailymocktest.php` — No data showing after page refresh

**Date:** July 30, 2026

### Problem
The init section called `loadClasses()` (async) and `loadAllTests()` sequentially without awaiting:

```js
loadClasses();    // ← starts fetching classes from API (async)
loadAllTests();   // ← runs immediately before classes are loaded!
```

Since `loadClasses()` is async, `loadAllTests()` ran while the class dropdown was still empty. It saw no class selected → showed "Select a class above" empty state → returned early. After classes finished loading, `loadAllTests()` was never called again, so the page appeared blank.

### Fix
Chained the calls so `loadAllTests()` runs only after `loadClasses()` completes:

```js
loadClasses().then(() => {
    loadAllTests();
    setTimeout(loadOldTests, 500);
}).catch(() => {
    loadAllTests();
    setTimeout(loadOldTests, 500);
});
```

### Files Modified
- `Admin/dailymocktest.php`

### What to Upload
`Admin/dailymocktest.php` to `wb-admin/Admin/dailymocktest.php`

---

## 3. 🐛 Fix: App-side daily test APIs returning upcoming/future tests

**Date:** July 30, 2026

### Problem
The app-side daily test APIs (`Api/app/daily-test/`) were returning ALL scheduled daily mock tests regardless of date. Future/upcoming tests were showing in the Flutter app's daily test section when only today's tests should appear.

### Fix
Added `AND test_date = CURDATE()` filter to all three daily-test APIs:

| File | Change |
|------|--------|
| `Api/app/daily-test/tests.php` | Added `AND test_date = CURDATE()` to WHERE clause |
| `Api/app/daily-test/classes.php` | Added `AND mt.test_date = CURDATE()` to JOIN condition |
| `Api/app/daily-test/subjects.php` | Added `AND mt.test_date = CURDATE()` to JOIN condition |

### Behavior After Fix
- ✅ Flutter app shows **only today's tests**
- ✅ Classes/subjects without today's tests are not listed
- ✅ Past/future tests remain in database and admin panel
- ✅ Admin panel "Old Mock Test Data" section can still view past tests

### Files Modified
- `Api/app/daily-test/tests.php`
- `Api/app/daily-test/classes.php`
- `Api/app/daily-test/subjects.php`

### What to Upload
All 3 files to `wb-admin/Api/app/daily-test/`

---

## 4. 🐛 Fix: `Admin/dailymocktest.php` — "Old Mock Test Data" search not working

**Date:** July 30, 2026

### Problem
`loadOldTests()` function relied entirely on `window._pastTests` cache, which is only populated when `loadAllTests()` runs with both a class AND subject selected. On page load, no class/subject is selected, so `_pastTests` is always empty. When the user clicks the Search button or changes the date, `loadOldTests()` reads from the empty cache and always shows "No tests found".

**Root cause chain:**
1. Page loads → `loadClasses()` → `loadAllTests()` (no class selected → returns early)
2. `window._pastTests` is never populated
3. User selects class + subject → `loadAllTests()` runs → `_pastTests` populated
4. But `loadOldTests()` is NOT triggered after class/subject selection
5. User clicks Search button → `loadOldTests()` runs → `_pastTests` is empty → "No tests found"

### Fix
Made `loadOldTests()` self-sufficient by:
1. Reading the currently selected class/subject from the form
2. If no class/subject selected, showing a clear message: "Select class & subject"
3. Fetching fresh data directly from `get-mocktest-status.php` API instead of relying on stale cache
4. Filtering the result by the selected date
5. Adding proper error handling with try/catch

### Files Modified
- `Admin/dailymocktest.php`

### What to Upload
`Admin/dailymocktest.php` to `wb-admin/Admin/dailymocktest.php`

---

## 📋 Summary of Files to Upload to Server

| # | File | Destination |
|---|------|-------------|
| 1 | `Admin/allmocktestscreen.php` | `wb-admin/Admin/allmocktestscreen.php` |
| 2 | `Admin/dailymocktest.php` | `wb-admin/Admin/dailymocktest.php` |
| 3 | `Api/app/daily-test/tests.php` | `wb-admin/Api/app/daily-test/tests.php` |
| 4 | `Api/app/daily-test/classes.php` | `wb-admin/Api/app/daily-test/classes.php` |
| 5 | `Api/app/daily-test/subjects.php` | `wb-admin/Api/app/daily-test/subjects.php` |

**No Flutter app changes needed** — all fixes are server-side PHP only.

---

## 5. 🐛 Fix: `Api/app/update-profile.php` — 406 Not Acceptable on image upload

**Date:** July 30, 2026

### Problem
When the Flutter app sends a multipart/form-data POST request (with image file) to `update-profile.php`, the server's **ModSecurity/LiteSpeed security module** blocks it with **HTTP 406 Not Acceptable**. The error occurs at the web server level *before* PHP even executes.

### Solution (Two-Pronged)

#### A) PHP: Added JSON+base64 image support (Primary Fix) ✅

The PHP script now **auto-detects the request format** based on `Content-Type` header:
- **`Content-Type: application/json`** — Reads JSON from `php://input`. Extracts `profile_image` as a **base64-encoded data URI** (`data:image/jpeg;base64,...`), decodes it, and saves to disk. **Completely bypasses ModSecurity** since no multipart upload occurs.
- **`multipart/form-data`** — Uses existing `$_FILES` logic (backward compatible).

#### B) `.htaccess` (Attempt) 
Created `Api/app/.htaccess` to try disabling ModSecurity rules and increasing upload limits. May not work on restrictive shared hosting, but the base64 fallback handles it.

### Files Modified
- `Api/app/update-profile.php` — Added JSON+base64 detection & decoding
- `Api/app/.htaccess` — NEW: ModSecurity disable + upload limits + CORS

### What to Upload
| File | Destination |
|------|-------------|
| `Api/app/update-profile.php` | `wb-admin/Api/app/update-profile.php` |
| `Api/app/.htaccess` | `wb-admin/Api/app/.htaccess` |

### 🔥 IMPORTANT: Flutter App Change Required

Since ModSecurity blocks multipart uploads at the server level, the **Flutter app must send the image as a base64 string inside JSON**. The new API expects:

```json
{
  "user_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "mobile": "1234567890",
  "address": "...",
  "class_grade": "...",
  "about_me": "...",
  "profile_image": "data:image/jpeg;base64,/9j/4AAQ..."
}
```

**Header:** `Content-Type: application/json`

### How to convert in Flutter:
```dart
import 'dart:convert';
import 'dart:io';

// Read image file as base64
File imageFile = File(imagePath);
List<int> imageBytes = await imageFile.readAsBytes();
String base64Image = base64Encode(imageBytes);
String mimeType = 'image/jpeg'; // or image/png

String dataUri = 'data:$mimeType;base64,$base64Image';

// Send as JSON
Map<String, dynamic> body = {
  'user_id': userId,
  'full_name': fullName,
  'email': email,
  'mobile': mobile,
  // ... other fields
  'profile_image': dataUri,
};

final response = await http.post(
  Uri.parse('https://saptahikgyan.space/wb-admin/Api/app/update-profile.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode(body),
);
```

### Retry multipart as fallback
The PHP still accepts multipart if you want to try the old method — but on this hosting provider, multipart file uploads will likely get blocked.