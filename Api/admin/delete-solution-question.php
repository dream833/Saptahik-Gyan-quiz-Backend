<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$solution_id = intval($data['solution_id'] ?? 0);

if ($solution_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Solution"
    ]);
    exit;

}

try {

    // Check Solution Exists

    $check = $pdo->prepare("
        SELECT id
        FROM solution_questions
        WHERE id = ?
    ");

    $check->execute([$solution_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Solution Not Found"
        ]);
        exit;

    }

    // Delete Solution

    $delete = $pdo->prepare("
        DELETE
        FROM solution_questions
        WHERE id = ?
    ");

    $delete->execute([$solution_id]);

    echo json_encode([
        "status" => true,
        "message" => "Solution Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}