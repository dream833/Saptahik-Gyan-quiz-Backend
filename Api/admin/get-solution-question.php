<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$chapter_id = intval($data['chapter_id'] ?? 0);
$question_type_id = intval($data['question_type_id'] ?? 0);

if (
    $chapter_id <= 0 ||
    $question_type_id <= 0
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;

}

try {

    // Check Chapter
    $chapter = $pdo->prepare("
        SELECT id
        FROM chapters
        WHERE id = ?
    ");

    $chapter->execute([$chapter_id]);

    if ($chapter->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Chapter Not Found"
        ]);
        exit;

    }

    // Check Question Type
    $type = $pdo->prepare("
        SELECT id
        FROM solution_question_types
        WHERE id = ?
    ");

    $type->execute([$question_type_id]);

    if ($type->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Type Not Found"
        ]);
        exit;

    }

    // Get Questions
    $stmt = $pdo->prepare("
        SELECT

            sq.id,
            sq.question,
            sq.answer,
            sq.created_at,
            sqt.type_name

        FROM solution_questions sq

        INNER JOIN solution_question_types sqt
            ON sq.question_type_id = sqt.id

        WHERE
            sq.chapter_id = ?
        AND
            sq.question_type_id = ?

        ORDER BY sq.id ASC
    ");

    $stmt->execute([
        $chapter_id,
        $question_type_id
    ]);

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

}z  