<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? ($_GET['class_id'] ?? 0));
$subject_id = intval($data['subject_id'] ?? ($_GET['subject_id'] ?? 0));

if ($class_id <= 0 || $subject_id <= 0) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            ch.id,
            ch.chapter_name AS name,
            COUNT(DISTINCT sq.id) AS question_count
        FROM chapters ch
        INNER JOIN subjects s ON s.id = ch.subject_id AND s.class_id = ?
        INNER JOIN solution_questions sq ON sq.chapter_id = ch.id
        WHERE ch.subject_id = ?
        GROUP BY ch.id, ch.chapter_name
        ORDER BY ch.chapter_name ASC
    ");

    $stmt->execute([$class_id, $subject_id]);

    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $chapters
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
