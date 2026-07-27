# Changes Made

## 1. Removed Start Time & End Time Fields (Daily Mock Test)

**Files modified:**
- `Admin/dailymocktest.php`
- `Api/admin/add-mocktest.php`
- `Api/admin/update-mock-test.php`

**What changed:**
- Removed "Start Time" and "End Time" input fields from the "Add New Mock Test" form
- Removed all JavaScript references to `startTime`/`endTime` in validation and API payloads
- Removed the time comparison check (`strtotime($start_time) >= strtotime($end_time)`)
- The database `start_time` and `end_time` columns are `NOT NULL`, so defaults are used:
  - `start_time` = `'00:00:00'`
  - `end_time` = `'23:59:00'`

**No SQL changes needed** — the mock_tests table already has these columns.

---

## 2. Fixed: Mock Tests Not Showing After Adding

**File modified:** `Admin/dailymocktest.php`

**What changed:**
- The `addMockTest()` function was calling `resetForm()` (which clears class/subject dropdowns) **before** `loadAllTests()`, so `loadAllTests()` couldn't query the API (it requires both class_id and subject_id).
- Fixed by saving the classId and subjectId before resetting the form, then passing them directly to `loadAllTests()`:
  ```js
  const savedClassId = classId;
  const savedSubjectId = subjectId;
  resetForm();
  loadAllTests(savedClassId, savedSubjectId);
  ```
- The `loadAllTests()` function now accepts optional `classId` and `subjectId` parameters. When provided, they override the (now-cleared) form select values.

**No SQL changes needed.**

---

## 3. Removed Duplicate Mock Test Restriction

**Files modified:**
- `Api/admin/add-mocktest.php`
- `Api/admin/update-mock-test.php`

**What changed:**
- Removed the duplicate check in `add-mocktest.php` that prevented creating multiple tests with the same `class_id + subject_id + test_date`
- Removed the duplicate check in `update-mock-test.php` that prevented editing a test to have the same `class_id + subject_id + test_name + test_date` as another existing test
- Multiple mock tests can now be added for the same class, subject, and date

**No SQL changes needed** — the `mock_tests` table has no UNIQUE constraints on these columns. Only the PHP code was enforcing uniqueness.

---

## Summary of All Modified Files

| File | Change |
|------|--------|
| `Admin/dailymocktest.php` | Removed Start/End Time fields; Fixed `loadAllTests()` to accept params |
| `Api/admin/add-mocktest.php` | Removed time validation & duplicate check |
| `Api/admin/update-mock-test.php` | Removed time validation & duplicate check |
