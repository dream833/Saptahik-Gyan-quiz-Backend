<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$category = trim($data['category'] ?? ($_GET['category'] ?? ''));
$year = intval($data['year'] ?? ($_GET['year'] ?? 0));
$subject_id = intval($data['subject_id'] ?? ($_GET['subject_id'] ?? 0));

if (empty($category) || $year <= 0 || $subject_id <= 0) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.year,
            p.pdf_file,
            s.subject_name
        FROM previous_year_questions p
        INNER JOIN exam_categories ec ON ec.id = p.exam_category_id
        INNER JOIN subjects s ON s.id = p.subject_id
        WHERE ec.title = ?
        AND p.year = ?
        AND p.subject_id = ?
        ORDER BY p.id ASC
    ");

    $stmt->execute([$category, $year, $subject_id]);

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $questions
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
