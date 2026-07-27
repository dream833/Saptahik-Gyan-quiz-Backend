<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? ($_GET['class_id'] ?? 0));
$subject_id = intval($data['subject_id'] ?? ($_GET['subject_id'] ?? 0));
$chapter_id = intval($data['chapter_id'] ?? ($_GET['chapter_id'] ?? 0));

if ($class_id <= 0 || $subject_id <= 0 || $chapter_id <= 0) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            s.id,
            s.set_name AS name,
            s.duration_minutes AS total_time,
            COUNT(a.id) AS total_questions
        FROM sets s
        LEFT JOIN all_mock_tests a ON a.set_id = s.id
        WHERE s.chapter_id = ?
        GROUP BY s.id, s.set_name, s.duration_minutes
        ORDER BY s.id ASC
    ");

    $stmt->execute([$chapter_id]);

    $sets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $sets
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
