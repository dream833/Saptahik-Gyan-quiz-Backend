<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$chapter_id = intval($data['chapter_id'] ?? 0);
$set_name = trim($data['set_name'] ?? '');
$duration_minutes = intval($data['duration_minutes'] ?? 0);

if (
    $chapter_id <= 0 ||
    empty($set_name) ||
    $duration_minutes <= 0
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;

}

try {

    // Check Chapter Exists
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

    // Duplicate Check
    $duplicate = $pdo->prepare("
        SELECT id
        FROM sets
        WHERE chapter_id = ?
        AND set_name = ?
    ");

    $duplicate->execute([
        $chapter_id,
        $set_name
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Set Already Exists"
        ]);
        exit;

    }

    // Insert Set
    $insert = $pdo->prepare("
        INSERT INTO sets
        (
            chapter_id,
            set_name,
            duration_minutes
        )
        VALUES
        (
            ?,?,?
        )
    ");

    $insert->execute([
        $chapter_id,
        $set_name,
        $duration_minutes
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Set Added Successfully",
        "set_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}