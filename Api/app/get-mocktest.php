<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$class_id   = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);

if (
    $class_id <= 0 ||
    $subject_id <= 0
) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid class or subject"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        id,
        test_name,
        duration_minutes,
        total_questions,
        total_marks
    FROM mock_tests
    WHERE class_id = ?
    AND subject_id = ?
    AND status = 'active'
    ORDER BY id DESC
");

$stmt->execute([
    $class_id,
    $subject_id
]);

$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => true,
    "data" => $tests
]);