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

    $stmt = $pdo->prepare("
        SELECT
            mt.*,
            c.class_name,
            s.subject_name
        FROM mock_tests mt
        INNER JOIN classes c ON c.id = mt.class_id
        INNER JOIN subjects s ON s.id = mt.subject_id
        WHERE mt.id = ?
        LIMIT 1
    ");

    $stmt->execute([$mock_test_id]);

    if ($stmt->rowCount() == 0) {
        echo json_encode([
            "status" => false,
            "message" => "Mock Test Not Found"
        ]);
        exit;
    }

    echo json_encode([
        "status" => true,
        "data" => $stmt->fetch(PDO::FETCH_ASSOC)
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}