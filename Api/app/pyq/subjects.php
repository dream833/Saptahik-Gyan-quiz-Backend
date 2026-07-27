<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$category = trim($data['category'] ?? ($_GET['category'] ?? ''));
$year = intval($data['year'] ?? ($_GET['year'] ?? 0));

if (empty($category) || $year <= 0) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT DISTINCT
            s.id,
            s.subject_name AS name,
            COUNT(DISTINCT p.id) AS question_count
        FROM subjects s
        INNER JOIN previous_year_questions p ON p.subject_id = s.id
        INNER JOIN exam_categories ec ON ec.id = p.exam_category_id
        WHERE ec.title = ?
        AND p.year = ?
        GROUP BY s.id, s.subject_name
        ORDER BY s.subject_name ASC
    ");

    $stmt->execute([$category, $year]);

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
