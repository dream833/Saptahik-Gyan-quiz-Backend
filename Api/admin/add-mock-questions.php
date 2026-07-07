<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

// Inputs
$mock_test_id   = intval($data['mock_test_id'] ?? 0);
$question       = trim($data['question'] ?? '');
$option_a       = trim($data['option_a'] ?? '');
$option_b       = trim($data['option_b'] ?? '');
$option_c       = trim($data['option_c'] ?? '');
$option_d       = trim($data['option_d'] ?? '');
$correct_answer = strtoupper(trim($data['correct_answer'] ?? ''));
$explanation    = trim($data['explanation'] ?? '');

// Validation
if (
    $mock_test_id <= 0 ||
    empty($question) ||
    empty($option_a) ||
    empty($option_b) ||
    empty($option_c) ||
    empty($option_d) ||
    empty($correct_answer)
) {
    echo json_encode([
        "status" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

if (!in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
    echo json_encode([
        "status" => false,
        "message" => "Correct answer must be A, B, C or D."
    ]);
    exit;
}

try {

    // Check Mock Test Exists
    $check = $pdo->prepare("
        SELECT id
        FROM mock_tests
        WHERE id = ?
    ");

    $check->execute([$mock_test_id]);

    if ($check->rowCount() == 0) {
        echo json_encode([
            "status" => false,
            "message" => "Mock Test Not Found."
        ]);
        exit;
    }

    // Duplicate Question Check
    $duplicate = $pdo->prepare("
        SELECT id
        FROM mock_test_questions
        WHERE mock_test_id = ?
        AND question = ?
    ");

    $duplicate->execute([
        $mock_test_id,
        $question
    ]);

    if ($duplicate->rowCount() > 0) {
        echo json_encode([
            "status" => false,
            "message" => "Question Already Exists."
        ]);
        exit;
    }

    // Insert Question
    $insert = $pdo->prepare("
        INSERT INTO mock_test_questions
        (
            mock_test_id,
            question,
            option_a,
            option_b,
            option_c,
            option_d,
            correct_answer,
            explanation
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?
        )
    ");

    $insert->execute([
        $mock_test_id,
        $question,
        $option_a,
        $option_b,
        $option_c,
        $option_d,
        $correct_answer,
        $explanation
    ]);

    $question_id = $pdo->lastInsertId();

    // Update Total Questions
    $count = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM mock_test_questions
        WHERE mock_test_id = ?
    ");

    $count->execute([$mock_test_id]);

    $total_questions = $count->fetch(PDO::FETCH_ASSOC)['total'];

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
        "message" => "Question Added Successfully.",
        "question_id" => $question_id,
        "total_questions" => $total_questions
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}