<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$set_id = intval($data['set_id'] ?? 0);
$class_id = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);
$chapter_id = intval($data['chapter_id'] ?? 0);

if ($set_id <= 0 || $class_id <= 0 || $subject_id <= 0 || $chapter_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "set_id, class_id, subject_id, and chapter_id are required"
    ]);
    exit;
}

try {

    // Get set info
    $setStmt = $pdo->prepare("
        SELECT id, set_name, duration_minutes
        FROM sets
        WHERE id = ? AND chapter_id = ?
        LIMIT 1
    ");
    $setStmt->execute([$set_id, $chapter_id]);
    $set = $setStmt->fetch(PDO::FETCH_ASSOC);

    if (!$set) {
        http_response_code(404);
        echo json_encode([
            "message" => "Set not found"
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
        FROM all_mock_tests
        WHERE set_id = ?
        ORDER BY id ASC
    ");
    $qStmt->execute([$set_id]);
    $questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format options as array
    foreach ($questions as &$q) {
        $q['options'] = [$q['option_a'], $q['option_b'], $q['option_c'], $q['option_d']];
        unset($q['option_a'], $q['option_b'], $q['option_c'], $q['option_d']);
    }
    unset($q);

    echo json_encode([
        "data" => [
            "set_id" => (int)$set['id'],
            "time_seconds" => (int)$set['duration_minutes'] * 60,
            "questions" => $questions
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
