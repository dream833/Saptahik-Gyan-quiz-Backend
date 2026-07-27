<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            class_name
        FROM classes
        ORDER BY class_name ASC
    ");

    $stmt->execute();

    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_classes" => count($classes),
        "data" => $classes
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
