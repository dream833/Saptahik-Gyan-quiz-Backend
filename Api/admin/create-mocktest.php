<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id     = intval($data['class_id'] ?? 0);
$subject_id   = intval($data['subject_id'] ?? 0);
$test_name    = trim($data['test_name'] ?? '');
$test_date    = trim($data['test_date'] ?? '');
$duration     = intval($data['duration_minutes'] ?? 0);
$description  = trim($data['description'] ?? '');

if ($class_id <= 0 || $subject_id <= 0 || empty($test_name)) {
    echo json_encode([
        "status" => false,
        "message" => "Class, Subject, and Test Name are required"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO mock_tests
    (
        test_name,
        test_date,
        duration_minutes,
        description,
        total_questions,
        total_marks,
        class_id,
        subject_id,
        status
    )
    VALUES
    (?, ?, ?, ?, 0, 0, ?, ?, 'active')
");

$stmt->execute([
    $test_name,
    $test_date ?: date('Y-m-d'),
    $duration ?: 30,
    $description,
    $class_id,
    $subject_id
]);

$test_id = $pdo->lastInsertId();

echo json_encode([
    "status" => true,
    "message" => "Mock test created successfully",
    "data" => [
        "id" => intval($test_id)
    ]
]);
