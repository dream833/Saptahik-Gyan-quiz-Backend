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

## 4. Fixed solution_suggestions Table — Added `description` Column

**File modified:** `quiz.sql` (schema)

**SQL to run:**
```sql
ALTER TABLE `solution_suggestions`
  ADD COLUMN `description` text DEFAULT NULL AFTER `title`;
```

**What changed:**
- The `add-solution-suggestion.php` API already sends a `description` field in the INSERT query, but the table was missing the `description` column, causing SQL errors.
- Added the `description` column to match the API.

---

## 5. New Table: `exam_categories` (for Previous Year Questions)

**SQL to run:**
```sql
CREATE TABLE `exam_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 6. New Table: `previous_year_questions` (stores PDF uploads)

**SQL to run:**
```sql
CREATE TABLE `previous_year_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_category_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `pdf_file` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_pyq_exam_category` (`exam_category_id`),
  KEY `fk_pyq_subject` (`subject_id`),
  CONSTRAINT `fk_pyq_exam_category` FOREIGN KEY (`exam_category_id`) REFERENCES `exam_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pyq_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 7. New API Endpoints Created

| API File | Purpose |
|----------|---------|
| `Api/admin/get-solution-suggestion.php` | Fetch suggestions by subject_id |
| `Api/admin/update-solution-suggestion.php` | Update a suggestion |
| `Api/admin/delete-solution-suggestion.php` | Delete a suggestion |
| `Api/admin/add-exam-category.php` | Add exam category for PYQ |
| `Api/admin/get-exam-category.php` | Get all exam categories |
| `Api/admin/update-exam-category.php` | Update an exam category |
| `Api/admin/delete-exam-category.php` | Delete an exam category |
| `Api/admin/add-previous-year-question.php` | Add PYQ with PDF upload |
| `Api/admin/get-previous-year-question.php` | Get PYQs by category + year |

---

## Summary of All Modified Files

| File | Change |
|------|--------|
| `Admin/dailymocktest.php` | Removed Start/End Time fields; Fixed `loadAllTests()` to accept params |
| `Api/admin/add-mocktest.php` | Removed time validation & duplicate check |
| `Api/admin/update-mock-test.php` | Removed time validation & duplicate check |
| `Admin/solutions.php` | Complete redesign with 3-tab interface (Q&A, Suggestions, Previous Year) |
| `Api/admin/add-solution-suggestion.php` | Fixed INSERT query to match table schema |
| `Api/admin/get-solution-suggestion.php` | **NEW** — Fetch suggestions by subject |
| `Api/admin/update-solution-suggestion.php` | **NEW** — Update suggestion |
| `Api/admin/delete-solution-suggestion.php` | **NEW** — Delete suggestion |
| `Api/admin/add-exam-category.php` | **NEW** — Add exam category |
| `Api/admin/get-exam-category.php` | **NEW** — Get exam categories |
| `Api/admin/update-exam-category.php` | **NEW** — Update exam category |
| `Api/admin/delete-exam-category.php` | **NEW** — Delete exam category |
| `Api/admin/add-previous-year-question.php` | **NEW** — Add PYQ with PDF |
| `Api/admin/get-previous-year-question.php` | **NEW** — Get PYQs |
| `Api/admin/delete-previous-year-question.php` | **NEW** — Delete PYQ (also removes PDF file) |
