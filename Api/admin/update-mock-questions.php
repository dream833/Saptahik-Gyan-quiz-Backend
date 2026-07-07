<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$question_id    = intval($data['question_id'] ?? 0);
$question       = trim($data['question'] ?? '');
$option_a       = trim($data['option_a'] ?? '');
$option_b       = trim($data['option_b'] ?? '');
$option_c       = trim($data['option_c'] ?? '');
$option_d       = trim($data['option_d'] ?? '');
$correct_answer = strtoupper(trim($data['correct_answer'] ?? ''));
$explanation    = trim($data['explanation'] ?? '');

if (
    $question_id <= 0 ||
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

if (!in_array($correct_answer, ['A','B','C','D'])) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Correct Answer."
    ]);
    exit;
}

try {

    // Question Exists
    $check = $pdo->prepare("
        SELECT id, mock_test_id
        FROM mock_test_questions
        WHERE id = ?
    ");

    $check->execute([$question_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Not Found."
        ]);
        exit;
    }

    $questionData = $check->fetch(PDO::FETCH_ASSOC);

    // Duplicate Question Check
    $duplicate = $pdo->prepare("
        SELECT id
        FROM mock_test_questions
        WHERE mock_test_id = ?
        AND question = ?
        AND id <> ?
    ");

    $duplicate->execute([
        $questionData['mock_test_id'],
        $question,
        $question_id
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Already Exists."
        ]);
        exit;
    }

    // Update Question
    $update = $pdo->prepare("
        UPDATE mock_test_questions
        SET
            question = ?,
            option_a = ?,
            option_b = ?,
            option_c = ?,
            option_d = ?,
            correct_answer = ?,
            explanation = ?
        WHERE id = ?
    ");

    $update->execute([
        $question,
        $option_a,
        $option_b,
        $option_c,
        $option_d,
        $correct_answer,
        $explanation,
        $question_id
    ]);

    if ($update->rowCount() > 0) {

        echo json_encode([
            "status" => true,
            "message" => "Question Updated Successfully."
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "No Changes Found."
        ]);

    }

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}