<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$mock_test_id = intval($data['mock_test_id'] ?? 0);

if ($mock_test_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Mock Test"
    ]);
    exit;

}

try {

    // Mock Test Details
    $mock = $pdo->prepare("
        SELECT
            mt.id,
            mt.test_name,
            mt.test_date,
            mt.start_time,
            mt.end_time,
            mt.duration_minutes,
            mt.total_questions,
            c.class_name,
            s.subject_name
        FROM mock_tests mt
        INNER JOIN classes c
            ON c.id = mt.class_id
        INNER JOIN subjects s
            ON s.id = mt.subject_id
        WHERE mt.id = ?
        LIMIT 1
    ");

    $mock->execute([$mock_test_id]);

    if ($mock->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Mock Test Not Found"
        ]);
        exit;

    }

    $mock_test = $mock->fetch(PDO::FETCH_ASSOC);

    // Question List
    $questions = $pdo->prepare("
        SELECT
            id,
            question,
            option_a,
            option_b,
            option_c,
            option_d,
            correct_answer,
            explanation,
            created_at
        FROM mock_test_questions
        WHERE mock_test_id = ?
        ORDER BY id ASC
    ");

    $questions->execute([$mock_test_id]);

    $question_list = $questions->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "mock_test" => $mock_test,
        "total_questions" => count($question_list),
        "questions" => $question_list
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}