<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? ($_GET['id'] ?? 0));

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "id is required"
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            ss.id,
            ss.title AS name,
            ss.answer AS content,
            ss.subject_id,
            s.class_id
        FROM solution_suggestions ss
        INNER JOIN subjects s ON s.id = ss.subject_id
        WHERE ss.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404);
        echo json_encode([
            "message" => "Suggestion not found"
        ]);
        exit;
    }

    echo json_encode([
        "data" => $item
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
