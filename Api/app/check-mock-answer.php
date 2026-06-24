<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$user_id = intval($data['user_id'] ?? 0);
$test_id = intval($data['test_id'] ?? 0);

if (
    $user_id <= 0 ||
    $test_id <= 0
) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT

        q.id AS question_id,
        q.question,

        q.option_a,
        q.option_b,
        q.option_c,
        q.option_d,

        q.correct_answer,

        ua.selected_answer

    FROM user_answers ua

    INNER JOIN questions q
        ON q.id = ua.question_id

    WHERE ua.user_id = ?
    AND ua.test_id = ?

    ORDER BY q.id ASC
");

$stmt->execute([
    $user_id,
    $test_id
]);

$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];

foreach ($answers as $row) {

    $result[] = [

        "question_id" => $row['question_id'],

        "question" => $row['question'],

        "option_a" => $row['option_a'],
        "option_b" => $row['option_b'],
        "option_c" => $row['option_c'],
        "option_d" => $row['option_d'],

        "selected_answer" => $row['selected_answer'],

        "correct_answer" => $row['correct_answer'],

        "is_correct" =>
            strtoupper($row['selected_answer']) ===
            strtoupper($row['correct_answer'])
    ];
}

echo json_encode([
    "status" => true,
    "data" => $result
]);