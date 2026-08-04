<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";
require_once "../../utils/notification_helper.php";

$data = json_decode(file_get_contents("php://input"), true);

$chapter_id = intval($data['chapter_id'] ?? 0);
$question_type_id = intval($data['question_type_id'] ?? 0);
$question = trim($data['question'] ?? '');
$answer = trim($data['answer'] ?? '');

if (
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

    // Duplicate Question

    $duplicate = $pdo->prepare("
        SELECT id
        FROM solution_questions
        WHERE
            chapter_id = ?
        AND
            question_type_id = ?
        AND
            question = ?
    ");

    $duplicate->execute([
        $chapter_id,
        $question_type_id,
        $question
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Already Exists"
        ]);
        exit;

    }

    // Insert

    $insert = $pdo->prepare("
        INSERT INTO solution_questions
        (
            chapter_id,
            question_type_id,
            question,
            answer
        )
        VALUES
        (
            ?,?,?,?
        )
    ");

    $insert->execute([
        $chapter_id,
        $question_type_id,
        $question,
        $answer
    ]);

    $newSolutionId = $pdo->lastInsertId();

    // Auto-notification: new solution question added
    // (plain substr/strlen — no mbstring dependency)
    $shortQuestion = strlen($question) > 60 ? substr($question, 0, 60) . "..." : $question;
    create_notification(
        $pdo,
        "New Solution Added",
        "A new question & answer has been added: \"" . $shortQuestion . "\"",
        "solution"
    );

    echo json_encode([
        "status" => true,
        "message" => "Question Added Successfully",
        "solution_id" => $newSolutionId
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}