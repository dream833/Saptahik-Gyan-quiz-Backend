<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? ($_GET['class_id'] ?? 0));

if ($class_id <= 0) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            s.id,
            s.subject_name AS name,
            COUNT(DISTINCT sq.id) AS question_count
        FROM subjects s
        INNER JOIN chapters ch ON ch.subject_id = s.id
        INNER JOIN solution_questions sq ON sq.chapter_id = ch.id
        WHERE s.class_id = ?
        GROUP BY s.id, s.subject_name
        ORDER BY s.subject_name ASC
    ");

    $stmt->execute([$class_id]);

    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $subjects
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
