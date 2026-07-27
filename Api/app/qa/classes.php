<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.class_name AS name,
            c.class_name AS grade,
            COUNT(DISTINCT s.id) AS subject_count,
            COUNT(DISTINCT sq.id) AS question_count
        FROM classes c
        INNER JOIN subjects s ON s.class_id = c.id
        INNER JOIN chapters ch ON ch.subject_id = s.id
        INNER JOIN solution_questions sq ON sq.chapter_id = ch.id
        GROUP BY c.id, c.class_name
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
