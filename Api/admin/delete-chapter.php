<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$chapter_id = intval($data['chapter_id'] ?? 0);

if ($chapter_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Chapter"
    ]);
    exit;

}

try {

    // Check Chapter Exists
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

    // Check Sets
    $set = $pdo->prepare("
        SELECT id
        FROM sets
        WHERE chapter_id = ?
        LIMIT 1
    ");

    $set->execute([$chapter_id]);

    if ($set->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Delete Sets First"
        ]);
        exit;

    }

    // Delete Chapter
    $delete = $pdo->prepare("
        DELETE
        FROM chapters
        WHERE id = ?
    ");

    $delete->execute([$chapter_id]);

    echo json_encode([
        "status" => true,
        "message" => "Chapter Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}