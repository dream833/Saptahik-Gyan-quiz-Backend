<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$question_id = intval($data['question_id'] ?? 0);
$test_id     = intval($data['test_id'] ?? 0);

if ($question_id <= 0 || $test_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Question ID and Test ID are required"
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Remove link from mock_test_questions
    $stmt = $pdo->prepare("
        DELETE FROM mock_test_questions
        WHERE test_id = ? AND question_id = ?
    ");
    $stmt->execute([$test_id, $question_id]);

    // Delete user answers for this question
    $pdo->prepare("DELETE FROM user_answers WHERE question_id = ?")->execute([$question_id]);

    // Delete the question itself
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);

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
        "message" => "Question deleted successfully",
        "data" => [
            "total_questions" => $totalQ
        ]
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();

    echo json_encode([
        "status" => false,
        "message" => "Failed to delete question: " . $e->getMessage()
    ]);
}
