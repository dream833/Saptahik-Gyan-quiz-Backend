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

    // Check Questions Exists
    $question = $pdo->prepare("
        SELECT id
        FROM all_mock_tests
        WHERE set_id = ?
        LIMIT 1
    ");

    $question->execute([$set_id]);

    if ($question->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Delete Questions First"
        ]);
        exit;

    }

    // Delete Set
    $delete = $pdo->prepare("
        DELETE
        FROM sets
        WHERE id = ?
    ");

    $delete->execute([$set_id]);

    echo json_encode([
        "status" => true,
        "message" => "Set Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}