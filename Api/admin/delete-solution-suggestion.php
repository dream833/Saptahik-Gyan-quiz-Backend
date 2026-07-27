<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$suggestion_id = intval($data['suggestion_id'] ?? 0);

if ($suggestion_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Suggestion"
    ]);
    exit;

}

try {

    // Check Suggestion Exists
    $check = $pdo->prepare("
        SELECT id
        FROM solution_suggestions
        WHERE id = ?
    ");

    $check->execute([$suggestion_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Suggestion Not Found"
        ]);
        exit;

    }

    // Delete
    $delete = $pdo->prepare("
        DELETE
        FROM solution_suggestions
        WHERE id = ?
    ");

    $delete->execute([$suggestion_id]);

    echo json_encode([
        "status" => true,
        "message" => "Suggestion Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
