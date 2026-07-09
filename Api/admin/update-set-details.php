<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$set_id = intval($data['set_id'] ?? 0);
$chapter_id = intval($data['chapter_id'] ?? 0);
$set_name = trim($data['set_name'] ?? '');
$duration_minutes = intval($data['duration_minutes'] ?? 0);

if (
    $set_id <= 0 ||
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

    // Check Set Exists
    $check = $pdo->prepare("
        SELECT id
        FROM sets
        WHERE id = ?
    ");

    $check->execute([$set_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Set Not Found"
        ]);
        exit;

    }

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
        AND id <> ?
    ");

    $duplicate->execute([
        $chapter_id,
        $set_name,
        $set_id
    ]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Set Already Exists"
        ]);
        exit;

    }

    // Update
    $update = $pdo->prepare("
        UPDATE sets
        SET
            chapter_id = ?,
            set_name = ?,
            duration_minutes = ?
        WHERE id = ?
    ");

    $update->execute([
        $chapter_id,
        $set_name,
        $duration_minutes,
        $set_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Set Updated Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}