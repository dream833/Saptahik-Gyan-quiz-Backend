<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

// This endpoint uses multipart/form-data for PDF upload
$exam_category_id = intval($_POST['exam_category_id'] ?? 0);
$year = intval($_POST['year'] ?? 0);
$subject_id = intval($_POST['subject_id'] ?? 0);

if (
    $exam_category_id <= 0 ||
    $year <= 0 ||
    $subject_id <= 0
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;

}

if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {

    echo json_encode([
        "status" => false,
        "message" => "PDF file is required"
    ]);
    exit;

}

$file = $_FILES['pdf_file'];

// Validate file type
$allowed_types = ['application/pdf'];
if (!in_array($file['type'], $allowed_types)) {

    echo json_encode([
        "status" => false,
        "message" => "Only PDF files are allowed"
    ]);
    exit;

}

// Validate file size (max 50MB)
$max_size = 50 * 1024 * 1024;
if ($file['size'] > $max_size) {

    echo json_encode([
        "status" => false,
        "message" => "File size exceeds 50MB limit"
    ]);
    exit;

}

try {

    // Check Exam Category Exists
    $checkCat = $pdo->prepare("
        SELECT id
        FROM exam_categories
        WHERE id = ?
    ");

    $checkCat->execute([$exam_category_id]);

    if ($checkCat->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Exam Category Not Found"
        ]);
        exit;

    }

    // Check Subject Exists
    $checkSub = $pdo->prepare("
        SELECT id
        FROM subjects
        WHERE id = ?
    ");

    $checkSub->execute([$subject_id]);

    if ($checkSub->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Subject Not Found"
        ]);
        exit;

    }

    // Create upload directory if it doesn't exist
    $upload_dir = "../../uploads/pyq/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "pyq_" . $exam_category_id . "_" . $year . "_" . $subject_id . "_" . time() . "." . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {

        echo json_encode([
            "status" => false,
            "message" => "Failed to upload file"
        ]);
        exit;

    }

    // Insert into database
    $insert = $pdo->prepare("
        INSERT INTO previous_year_questions
        (
            exam_category_id,
            year,
            subject_id,
            pdf_file
        )
        VALUES
        (
            ?,?,?,?
        )
    ");

    $insert->execute([
        $exam_category_id,
        $year,
        $subject_id,
        $filename
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Previous Year Question Added Successfully",
        "pyq_id" => $pdo->lastInsertId(),
        "pdf_file" => $filename
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
