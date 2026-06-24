<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$test_id = intval($data['test_id'] ?? 0);

if ($test_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid test id"
    ]);

    exit;
}

$stmt = $pdo->prepare("
    SELECT
        id,
        test_name,
        duration_minutes,
        total_questions
    FROM mock_tests
    WHERE id = ?
    AND status = 'active'
    LIMIT 1
");

$stmt->execute([$test_id]);

$test = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$test) {

    echo json_encode([
        "status" => false,
        "message" => "Test not found"
    ]);

    exit;
}

$stmt = $pdo->prepare("
    SELECT
        q.id,
        q.question,
        q.option_a,
        q.option_b,
        q.option_c,
        q.option_d
    FROM mock_test_questions mtq

    INNER JOIN questions q
    ON q.id = mtq.question_id

    WHERE mtq.test_id = ?
");

$stmt->execute([$test_id]);

$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => true,
    "data" => [
        "test_id" => $test['id'],
        "test_name" => $test['test_name'],
        "duration_minutes" => $test['duration_minutes'],
        "total_questions" => count($questions),
        "questions" => $questions
    ]
]);