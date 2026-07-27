<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? ($_GET['class_id'] ?? 0));
$subject_id = intval($data['subject_id'] ?? ($_GET['subject_id'] ?? 0));
$chapter_id = intval($data['chapter_id'] ?? ($_GET['chapter_id'] ?? 0));
$type = trim($data['type'] ?? ($_GET['type'] ?? ''));

if ($class_id <= 0 || $subject_id <= 0 || $chapter_id <= 0 || empty($type)) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    // Map type string to type_name
    $typeMap = [
        'veryShort' => 'Very Short',
        'explanatory' => 'Explanatory',
        'essay' => 'Essay-Type'
    ];

    $typeName = $typeMap[$type] ?? $type;

    $stmt = $pdo->prepare("
        SELECT
            sq.id,
            sq.question,
            sq.answer,
            LOWER(REPLACE(sqt.type_name, ' ', '')) AS type,
            ch.subject_id,
            sq.chapter_id,
            s.class_id
        FROM solution_questions sq
        INNER JOIN solution_question_types sqt ON sqt.id = sq.question_type_id
        INNER JOIN chapters ch ON ch.id = sq.chapter_id
        INNER JOIN subjects s ON s.id = ch.subject_id
        WHERE s.class_id = ?
        AND ch.subject_id = ?
        AND sq.chapter_id = ?
        AND sqt.type_name = ?
        ORDER BY sq.id ASC
    ");

    $stmt->execute([$class_id, $subject_id, $chapter_id, $typeName]);

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
