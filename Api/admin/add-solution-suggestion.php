<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";
require_once "../../utils/notification_helper.php";

$data = json_decode(file_get_contents("php://input"), true);

$subject_id = intval($data['subject_id'] ?? 0);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$answer = trim($data['answer'] ?? '');

if (
    $subject_id <= 0 ||
    empty($title) ||
    empty($answer)
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;

}

try {

    // Check Subject Exists

    $subject = $pdo->prepare("
        SELECT id
        FROM subjects
        WHERE id = ?
    ");

    $subject->execute([$subject_id]);

    if ($subject->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Subject Not Found"
        ]);
        exit;

    }

    // Duplicate Check

    $duplicate = $pdo->prepare("
        SELECT id
        FROM solution_suggestions
        WHERE
            subject_id = ?
        AND
            title = ?
    ");

    $duplicate->execute([
        $subject_id,
        $title
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Suggestion Already Exists"
        ]);
        exit;

    }

    // Insert

    $insert = $pdo->prepare("
        INSERT INTO solution_suggestions
        (
            subject_id,
            title,
            description,
            answer
        )
        VALUES
        (
            ?,?,?,?
        )
    ");

    $insert->execute([
        $subject_id,
        $title,
        $description,
        $answer
    ]);

    $newSuggestionId = $pdo->lastInsertId();

    // Auto-notification: new suggestion added
    create_notification(
        $pdo,
        "New Suggestion",
        "A new suggestion has been published: \"" . $title . "\"",
        "solution"
    );

    echo json_encode([
        "status" => true,
        "message" => "Suggestion Added Successfully",
        "suggestion_id" => $newSuggestionId
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}