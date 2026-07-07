<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$question_id = intval($data['question_id'] ?? 0);

if ($question_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Question"
    ]);
    exit;
}

try {

    // Check Question Exists
    $check = $pdo->prepare("
        SELECT
            id,
            mock_test_id
        FROM mock_test_questions
        WHERE id = ?
        LIMIT 1
    ");

    $check->execute([$question_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Not Found"
        ]);
        exit;

    }

    $question = $check->fetch(PDO::FETCH_ASSOC);

    $mock_test_id = $question['mock_test_id'];

    // Delete Question
    $delete = $pdo->prepare("
        DELETE
        FROM mock_test_questions
        WHERE id = ?
    ");

    $delete->execute([$question_id]);

    // Update Total Questions
    $count = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM mock_test_questions
        WHERE mock_test_id = ?
    ");

    $count->execute([$mock_test_id]);

    $total_questions = $count->fetch(PDO::FETCH_ASSOC)['total'];

    // Update Mock Test
    $update = $pdo->prepare("
        UPDATE mock_tests
        SET total_questions = ?
        WHERE id = ?
    ");

    $update->execute([
        $total_questions,
        $mock_test_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Question Deleted Successfully",
        "total_questions" => $total_questions
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}