<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$class_id = intval($data['class_id'] ?? 0);

if ($class_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid class id"
    ]);

    exit;
}

$stmt = $pdo->prepare("
    SELECT
        id,
        subject_name
    FROM subjects
    WHERE class_id = ?
    ORDER BY subject_name ASC
");

$stmt->execute([$class_id]);

$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => true,
    "data" => $subjects
]);