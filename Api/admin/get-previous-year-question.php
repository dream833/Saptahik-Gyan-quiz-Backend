<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$exam_category_id = intval($data['exam_category_id'] ?? 0);
$year = intval($data['year'] ?? 0);

if ($exam_category_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Exam Category"
    ]);
    exit;

}

try {

    // Check Exam Category Exists
    $checkCat = $pdo->prepare("
        SELECT id
        FROM exam_categories
        WHERE id = ?
    ");

    $checkCat->execute([$exam_category_id]);

    if ($checkCat->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Exam Category Not Found"
        ]);
        exit;

    }

    // Build query
    $sql = "
        SELECT
            pq.id,
            pq.year,
            pq.pdf_file,
            pq.created_at,
            s.id AS subject_id,
            s.subject_name,
            cls.id AS class_id,
            cls.class_name
        FROM previous_year_questions pq
        INNER JOIN subjects s ON s.id = pq.subject_id
        INNER JOIN classes cls ON cls.id = s.class_id
        WHERE pq.exam_category_id = ?
    ";

    $params = [$exam_category_id];

    if ($year > 0) {
        $sql .= " AND pq.year = ?";
        $params[] = $year;
    }

    $sql .= " ORDER BY pq.year DESC, pq.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add PDF URL
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/uploads/pyq/';

    foreach ($questions as &$q) {
        $q['pdf_url'] = $base_url . $q['pdf_file'];
    }

    echo json_encode([
        "status" => true,
        "total_questions" => count($questions),
        "data" => $questions
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
