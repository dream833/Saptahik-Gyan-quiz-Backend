<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$test_id = intval($data['test_id'] ?? 0);
$question_text = trim($data['question'] ?? '');
$option_a = trim($data['option_a'] ?? '');
$option_b = trim($data['option_b'] ?? '');
$option_c = trim($data['option_c'] ?? '');
$option_d = trim($data['option_d'] ?? '');
$correct_answer = strtoupper(trim($data['correct_answer'] ?? ''));

if (
    $test_id <= 0 ||
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

try {
    $pdo->beginTransaction();

    // Insert into questions table
    $stmt = $pdo->prepare("
        INSERT INTO questions
        (question, option_a, option_b, option_c, option_d, correct_answer)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $question_text,
        $option_a,
        $option_b,
        $option_c,
        $option_d,
        $correct_answer
    ]);

    $question_id = intval($pdo->lastInsertId());

    // Link to test
    $stmt = $pdo->prepare("
        INSERT INTO mock_test_questions (test_id, question_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$test_id, $question_id]);

    // Update total_questions count
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM mock_test_questions WHERE test_id = ?
    ");
    $countStmt->execute([$test_id]);
    $totalQ = intval($countStmt->fetchColumn());

    $pdo->prepare("
        UPDATE mock_tests SET total_questions = ? WHERE id = ?
    ")->execute([$totalQ, $test_id]);

    $pdo->commit();

    echo json_encode([
        "status" => true,
        "message" => "Question added successfully",
        "data" => [
            "question_id" => $question_id,
            "total_questions" => $totalQ
        ]
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();

    echo json_encode([
        "status" => false,
        "message" => "Failed to add question: " . $e->getMessage()
    ]);
}
