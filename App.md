# 📱 App API Connection Guide

> How to connect the Flutter app to the backend APIs.

---

## 🔄 Profile Update API

### Endpoint
```
POST https://saptahikgyan.space/wb-admin/Api/app/update-profile.php
```

### 📌 Change: Form-data → JSON+base64

| Before (❌ Blocked by server) | After (✅ Works) |
|-------------------------------|------------------|
| `Content-Type: multipart/form-data` | `Content-Type: application/json` |
| Image sent as **file** in `$_FILES` | Image sent as **base64 string** in JSON |
| Blocked by ModSecurity (406 error) | ✅ Bypasses ModSecurity |

### Request Format

**Header:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "user_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "mobile": "1234567890",
  "address": "123 Street, City",
  "class_grade": "Class 10",
  "about_me": "Student",
  "profile_image": "data:image/jpeg;base64,/9j/4AAQ..."
}
```

> **Note:** `profile_image` is **optional**. Send it only when changing the image.

### Response (Success)
```json
{
  "status": true,
  "message": "Profile updated successfully"
}
```

### Response (Error)
```json
{
  "status": false,
  "message": "Required fields missing"
}
```

### ✅ Flutter Dart Code

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

Future<bool> updateProfile({
  required int userId,
  required String fullName,
  required String email,
  required String mobile,
  String? address,
  String? classGrade,
  String? aboutMe,
  File? imageFile,           // optional image
}) async {
  final url = Uri.parse(
    'https://saptahikgyan.space/wb-admin/Api/app/update-profile.php'
  );

  // Build JSON body
  Map<String, dynamic> body = {
    'user_id': userId,
    'full_name': fullName,
    'email': email,
    'mobile': mobile,
  };

  // Optional fields
  if (address != null) body['address'] = address;
  if (classGrade != null) body['class_grade'] = classGrade;
  if (aboutMe != null) body['about_me'] = aboutMe;

  // Convert image to base64 data URI (if provided)
  if (imageFile != null) {
    List<int> imageBytes = await imageFile.readAsBytes();
    String base64Str = base64Encode(imageBytes);
    String mimeType = _getMimeType(imageFile.path);
    body['profile_image'] = 'data:$mimeType;base64,$base64Str';
  }

  // Send as JSON (NOT multipart)
  final response = await http.post(
    url,
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode(body),
  );

  if (response.statusCode == 200) {
    final result = jsonDecode(response.body);
    return result['status'] == true;
  }
  return false;
}

String _getMimeType(String path) {
  if (path.endsWith('.png')) return 'image/png';
  if (path.endsWith('.webp')) return 'image/webp';
  if (path.endsWith('.gif')) return 'image/gif';
  return 'image/jpeg'; // default
}
```

### ⚠️ Important Notes

1. **Always send `Content-Type: application/json`** in the header
2. **Image format**: `data:image/{type};base64,{base64string}`
3. **Supported image types**: `jpg`, `jpeg`, `png`, `webp`
4. **Max image size**: ~10MB (server-side limit via `.htaccess`)
5. **Don't send `profile_image`** if no new image is selected — the server will keep the existing one
6. **Old form-data method no longer works** — the server blocks multipart uploads with 406 error

---

## 📋 Other App APIs

For reference, all other app APIs use **JSON format** (same as above):

| Endpoint | Method | Description |
|----------|--------|-------------|
| `login.php` | POST | User login |
| `signup.php` | POST | User registration |
| `fetch-profile.php` | POST | Get user profile (returns `profile_image` path) |
| `update-profile.php` | POST | ✅ Update profile (JSON+base64 — see above) |
| `get-class.php` | POST | Get available classes |
| `get-subject.php` | POST | Get subjects by class |
| `get-mocktest.php` | POST | Get mock tests |
| `start-mocktest.php` | POST | Start a mock test |
| `submit-mocktest.php` | POST | Submit mock test answers |
| `check-mock-answer.php` | POST | Check individual answer |
| `dashboard.php` | POST | Get user dashboard data |
