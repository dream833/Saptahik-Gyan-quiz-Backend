<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$set_id = intval($data['set_id'] ?? 0);

if ($set_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Set"
    ]);
    exit;

}

try {

    // Check Set Exists

    $check = $pdo->prepare("
        SELECT id
        FROM sets
        WHERE id = ?
    ");

    $check->execute([$set_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Set Not Found"
        ]);
        exit;

    }

    // Question List

    $stmt = $pdo->prepare("
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

        FROM all_mock_tests

        WHERE set_id = ?

        ORDER BY id ASC
    ");

    $stmt->execute([$set_id]);

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_questions" => count($questions),
        "data" => $questions
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}