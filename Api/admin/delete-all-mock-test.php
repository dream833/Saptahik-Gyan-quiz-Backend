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
        SELECT id
        FROM all_mock_tests
        WHERE id = ?
    ");

    $check->execute([$question_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Not Found"
        ]);
        exit;

    }

    // Delete Question

    $delete = $pdo->prepare("
        DELETE
        FROM all_mock_tests
        WHERE id = ?
    ");

    $delete->execute([$question_id]);

    echo json_encode([
        "status" => true,
        "message" => "Question Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}