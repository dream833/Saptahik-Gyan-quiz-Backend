<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$solution_id = intval($data['solution_id'] ?? 0);
$chapter_id = intval($data['chapter_id'] ?? 0);
$question_type_id = intval($data['question_type_id'] ?? 0);

$question = trim($data['question'] ?? '');
$answer = trim($data['answer'] ?? '');

if (
    $solution_id <= 0 ||
    $chapter_id <= 0 ||
    $question_type_id <= 0 ||
    empty($question) ||
    empty($answer)
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;

}

try {

    // Check Solution Exists

    $check = $pdo->prepare("
        SELECT id
        FROM solution_questions
        WHERE id = ?
    ");

    $check->execute([$solution_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Solution Not Found"
        ]);
        exit;

    }

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

    // Duplicate Check

    $duplicate = $pdo->prepare("
        SELECT id
        FROM solution_questions
        WHERE
            chapter_id = ?
        AND
            question_type_id = ?
        AND
            question = ?
        AND
            id <> ?
    ");

    $duplicate->execute([
        $chapter_id,
        $question_type_id,
        $question,
        $solution_id
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Already Exists"
        ]);
        exit;

    }

    // Update

    $update = $pdo->prepare("
        UPDATE solution_questions
        SET
            chapter_id = ?,
            question_type_id = ?,
            question = ?,
            answer = ?
        WHERE id = ?
    ");

    $update->execute([
        $chapter_id,
        $question_type_id,
        $question,
        $answer,
        $solution_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Solution Updated Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}