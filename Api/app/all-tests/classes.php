<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT DISTINCT
            c.id,
            c.class_name AS name,
            c.class_name AS grade,
            CASE WHEN COUNT(s.id) > 0 THEN 1 ELSE 0 END AS has_tests
        FROM classes c
        LEFT JOIN subjects s ON s.class_id = c.id
        LEFT JOIN chapters ch ON ch.subject_id = s.id
        LEFT JOIN sets st ON st.chapter_id = ch.id
        GROUP BY c.id, c.class_name
        HAVING has_tests = 1
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
