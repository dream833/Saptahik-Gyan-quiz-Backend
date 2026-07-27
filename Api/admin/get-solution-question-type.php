<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            type_name
        FROM solution_question_types
        ORDER BY id ASC
    ");

    $stmt->execute();

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_types" => count($types),
        "data" => $types
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
