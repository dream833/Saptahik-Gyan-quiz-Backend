<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$question_id  = intval($data['question_id'] ?? 0);
$question_text = trim($data['question'] ?? '');
$option_a     = trim($data['option_a'] ?? '');
$option_b     = trim($data['option_b'] ?? '');
$option_c     = trim($data['option_c'] ?? '');
$option_d     = trim($data['option_d'] ?? '');
$correct_answer = strtoupper(trim($data['correct_answer'] ?? ''));

if (
    $question_id <= 0 ||
    empty($question_text) ||
    empty($option_a) ||
    empty($option_b) ||
    empty($option_c) ||
    empty($option_d) ||
    !in_array($correct_answer, ['A', 'B', 'C', 'D'])
) {
    echo json_encode([
        "status" => false,
        "message" => "All fields are required. Correct answer must be A, B, C, or D."
    ]);
    exit;
}

// Verify question exists
$stmt = $pdo->prepare("SELECT id FROM questions WHERE id = ? LIMIT 1");
$stmt->execute([$question_id]);
if (!$stmt->fetch()) {
    echo json_encode([
        "status" => false,
        "message" => "Question not found"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE questions
    SET
        question       = ?,
        option_a       = ?,
        option_b       = ?,
        option_c       = ?,
        option_d       = ?,
        correct_answer = ?
    WHERE id = ?
");

$stmt->execute([
    $question_text,
    $option_a,
    $option_b,
    $option_c,
    $option_d,
    $correct_answer,
    $question_id
]);

echo json_encode([
    "status" => true,
    "message" => "Question updated successfully"
]);
