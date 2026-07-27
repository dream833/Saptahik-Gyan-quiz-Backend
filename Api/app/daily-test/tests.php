<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? ($_GET['class_id'] ?? 0));
$subject_id = intval($data['subject_id'] ?? ($_GET['subject_id'] ?? 0));

if ($class_id <= 0 || $subject_id <= 0) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            test_name AS name,
            description,
            total_questions,
            (duration_minutes * 60) AS time_seconds,
            2 AS mark_per_question
        FROM mock_tests
        WHERE class_id = ?
        AND subject_id = ?
        AND is_daily = 1
        AND status = 'scheduled'
        ORDER BY test_date ASC, start_time ASC
    ");

    $stmt->execute([$class_id, $subject_id]);

    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $tests
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
