<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$subject_id = intval($data['subject_id'] ?? 0);
$chapter_name = trim($data['chapter_name'] ?? '');

if (
    $subject_id <= 0 ||
    empty($chapter_name)
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
        FROM chapters
        WHERE
            subject_id = ?
        AND
            chapter_name = ?
    ");

    $duplicate->execute([
        $subject_id,
        $chapter_name
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Chapter Already Exists"
        ]);
        exit;

    }

    // Insert Chapter
    $insert = $pdo->prepare("
        INSERT INTO chapters
        (
            subject_id,
            chapter_name
        )
        VALUES
        (
            ?,?
        )
    ");

    $insert->execute([
        $subject_id,
        $chapter_name
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Chapter Added Successfully",
        "chapter_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}