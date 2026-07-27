<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$test_id = intval($data['test_id'] ?? 0);
$class_id = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);

if ($test_id <= 0 || $class_id <= 0 || $subject_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "test_id, class_id, and subject_id are required"
    ]);
    exit;
}

try {

    // Get test info
    $testStmt = $pdo->prepare("
        SELECT id, test_name, duration_minutes, total_questions
        FROM mock_tests
        WHERE id = ? AND class_id = ? AND subject_id = ? AND is_daily = 1
        LIMIT 1
    ");
    $testStmt->execute([$test_id, $class_id, $subject_id]);
    $test = $testStmt->fetch(PDO::FETCH_ASSOC);

    if (!$test) {
        http_response_code(404);
        echo json_encode([
            "message" => "Test not found"
        ]);
        exit;
    }

    // Get questions
    $qStmt = $pdo->prepare("
        SELECT
            id,
            question AS text,
            option_a,
            option_b,
            option_c,
            option_d,
            CASE correct_answer
                WHEN 'A' THEN 0
                WHEN 'B' THEN 1
                WHEN 'C' THEN 2
                WHEN 'D' THEN 3
            END AS correct_index
        FROM mock_test_questions
        WHERE mock_test_id = ?
        ORDER BY id ASC
    ");
    $qStmt->execute([$test_id]);
    $questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format options as array
    foreach ($questions as &$q) {
        $q['options'] = [$q['option_a'], $q['option_b'], $q['option_c'], $q['option_d']];
        unset($q['option_a'], $q['option_b'], $q['option_c'], $q['option_d']);
    }
    unset($q);

    echo json_encode([
        "data" => [
            "test_id" => (int)$test['id'],
            "time_seconds" => (int)$test['duration_minutes'] * 60,
            "questions" => $questions
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
