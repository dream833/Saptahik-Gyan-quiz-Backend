<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$test_id = intval($data['test_id'] ?? 0);

if ($test_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid test id"
    ]);
    exit;
}

// Fetch test details
$stmt = $pdo->prepare("
    SELECT
        mt.id,
        mt.test_name AS name,
        mt.test_date AS date,
        mt.duration_minutes AS duration,
        mt.description,
        mt.total_questions,
        mt.class_id,
        mt.subject_id,
        c.class_name AS className,
        s.subject_name AS subjectName
    FROM mock_tests mt
    LEFT JOIN classes c ON mt.class_id = c.id
    LEFT JOIN subjects s ON mt.subject_id = s.id
    WHERE mt.id = ?
    LIMIT 1
");

$stmt->execute([$test_id]);
$test = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$test) {
    echo json_encode([
        "status" => false,
        "message" => "Mock test not found"
    ]);
    exit;
}

// Fetch questions linked to this test
$stmt = $pdo->prepare("
    SELECT
        q.id,
        q.question AS text,
        q.option_a,
        q.option_b,
        q.option_c,
        q.option_d,
        q.correct_answer AS correct
    FROM mock_test_questions mtq
    INNER JOIN questions q ON q.id = mtq.question_id
    WHERE mtq.test_id = ?
    ORDER BY q.id ASC
");

$stmt->execute([$test_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format options into array for consistency
$formattedQuestions = [];
foreach ($questions as $q) {
    $formattedQuestions[] = [
        "id" => intval($q['id']),
        "text" => $q['text'],
        "options" => [
            $q['option_a'],
            $q['option_b'],
            $q['option_c'],
            $q['option_d']
        ],
        "correct" => $q['correct']
    ];
}

$test['questions'] = $formattedQuestions;
$test['questions_count'] = count($formattedQuestions);

echo json_encode([
    "status" => true,
    "data" => $test
]);
