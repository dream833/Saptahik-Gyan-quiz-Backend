<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            description,
            created_at
        FROM exam_categories
        ORDER BY title ASC
    ");

    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_categories" => count($categories),
        "data" => $categories
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
