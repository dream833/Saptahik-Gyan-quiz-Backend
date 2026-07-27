<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$suggestion_id = intval($data['suggestion_id'] ?? 0);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$answer = trim($data['answer'] ?? '');

if (
    $suggestion_id <= 0 ||
    empty($title) ||
    empty($answer)
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
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

    // Update
    $update = $pdo->prepare("
        UPDATE solution_suggestions
        SET
            title = ?,
            description = ?,
            answer = ?
        WHERE id = ?
    ");

    $update->execute([
        $title,
        $description,
        $answer,
        $suggestion_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Suggestion Updated Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
