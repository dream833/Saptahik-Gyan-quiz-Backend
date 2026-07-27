<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$subject_id = intval($data['subject_id'] ?? 0);

if ($subject_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Subject"
    ]);
    exit;

}

try {

    // Check Subject Exists
    $check = $pdo->prepare("
        SELECT id
        FROM subjects
        WHERE id = ?
    ");

    $check->execute([$subject_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Subject Not Found"
        ]);
        exit;

    }

    // Get Suggestions
    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            description,
            answer,
            created_at
        FROM solution_suggestions
        WHERE subject_id = ?
        ORDER BY id ASC
    ");

    $stmt->execute([$subject_id]);

    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_suggestions" => count($suggestions),
        "data" => $suggestions
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
