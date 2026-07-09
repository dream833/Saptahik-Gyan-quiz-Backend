<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$chapter_id = intval($data['chapter_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);
$chapter_name = trim($data['chapter_name'] ?? '');

if (
    $chapter_id <= 0 ||
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

    // Chapter Exists
    $check = $pdo->prepare("
        SELECT id
        FROM chapters
        WHERE id = ?
    ");

    $check->execute([$chapter_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Chapter Not Found"
        ]);
        exit;

    }

    // Subject Exists
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
        AND
            id <> ?
    ");

    $duplicate->execute([
        $subject_id,
        $chapter_name,
        $chapter_id
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Chapter Already Exists"
        ]);
        exit;

    }

    // Update
    $update = $pdo->prepare("
        UPDATE chapters
        SET
            subject_id = ?,
            chapter_name = ?
        WHERE id = ?
    ");

    $update->execute([
        $subject_id,
        $chapter_name,
        $chapter_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Chapter Updated Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}