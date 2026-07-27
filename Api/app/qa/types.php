<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            CASE type_name
                WHEN 'Very Short' THEN 'veryShort'
                WHEN 'Explanatory' THEN 'explanatory'
                WHEN 'Essay-Type' THEN 'essay'
            END AS type,
            type_name AS label,
            CASE type_name
                WHEN 'Very Short' THEN 'Brief one-line answers'
                WHEN 'Explanatory' THEN 'Detailed explanations'
                WHEN 'Essay-Type' THEN 'Long-form descriptive answers'
            END AS description
        FROM solution_question_types
        ORDER BY id ASC
    ");

    $stmt->execute();

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $types
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
