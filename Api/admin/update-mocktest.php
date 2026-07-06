<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$test_id      = intval($data['test_id'] ?? 0);
$class_id     = intval($data['class_id'] ?? 0);
$subject_id   = intval($data['subject_id'] ?? 0);
$test_name    = trim($data['test_name'] ?? '');
$test_date    = trim($data['test_date'] ?? '');
$duration     = intval($data['duration_minutes'] ?? 0);
$description  = trim($data['description'] ?? '');

if ($test_id <= 0 || $class_id <= 0 || $subject_id <= 0 || empty($test_name)) {
    echo json_encode([
        "status" => false,
        "message" => "Test ID, Class, Subject, and Test Name are required"
    ]);
    exit;
}

// Verify test exists
$stmt = $pdo->prepare("SELECT id FROM mock_tests WHERE id = ? LIMIT 1");
$stmt->execute([$test_id]);
if (!$stmt->fetch()) {
    echo json_encode([
        "status" => false,
        "message" => "Mock test not found"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE mock_tests
    SET
        test_name       = ?,
        test_date       = ?,
        duration_minutes = ?,
        description     = ?,
        class_id        = ?,
        subject_id      = ?
    WHERE id = ?
");

$stmt->execute([
    $test_name,
    $test_date ?: date('Y-m-d'),
    $duration ?: 30,
    $description,
    $class_id,
    $subject_id,
    $test_id
]);

echo json_encode([
    "status" => true,
    "message" => "Mock test updated successfully"
]);
