<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$set_id = intval($data['set_id'] ?? 0);

$question = trim($data['question'] ?? '');

$option_a = trim($data['option_a'] ?? '');
$option_b = trim($data['option_b'] ?? '');
$option_c = trim($data['option_c'] ?? '');
$option_d = trim($data['option_d'] ?? '');

$correct_answer = strtoupper(trim($data['correct_answer'] ?? ''));

$explanation = trim($data['explanation'] ?? '');

if (
    $set_id <= 0 ||
    empty($question) ||
    empty($option_a) ||
    empty($option_b) ||
    empty($option_c) ||
    empty($option_d) ||
    !in_array($correct_answer, ['A','B','C','D'])
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;

}

try {

    // Check Set Exists

    $set = $pdo->prepare("
        SELECT id
        FROM sets
        WHERE id = ?
    ");

    $set->execute([$set_id]);

    if ($set->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Set Not Found"
        ]);
        exit;

    }

    // Duplicate Question

    $duplicate = $pdo->prepare("
        SELECT id
        FROM all_mock_tests
        WHERE
            set_id = ?
        AND
            question = ?
    ");

    $duplicate->execute([
        $set_id,
        $question
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Already Exists"
        ]);
        exit;

    }

    // Insert Question

    $insert = $pdo->prepare("
        INSERT INTO all_mock_tests
        (
            set_id,
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

        $set_id,
        $question,
        $option_a,
        $option_b,
        $option_c,
        $option_d,
        $correct_answer,
        $explanation

    ]);

    echo json_encode([

        "status" => true,
        "message" => "Question Added Successfully",
        "question_id" => $pdo->lastInsertId()

    ]);

} catch (PDOException $e) {

    echo json_encode([

        "status" => false,
        "message" => $e->getMessage()

    ]);

}