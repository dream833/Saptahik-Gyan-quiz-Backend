<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT DISTINCT
            c.id,
            c.class_name AS name
        FROM classes c
        INNER JOIN mock_tests mt ON mt.class_id = c.id
        WHERE mt.is_daily = 1
        ORDER BY c.class_name ASC
    ");

    $stmt->execute();

    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($classes)) {
        echo json_encode([
            "data" => []
        ]);
        exit;
    }

    echo json_encode([
        "data" => $classes
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
