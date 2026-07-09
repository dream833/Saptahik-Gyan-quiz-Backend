<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$subject_id = intval($data['subject_id'] ?? 0);

if ($subject_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Subject"
    ]);
    exit;

}

try {

    // Check Subject Exists
    $check = $pdo->prepare("
        SELECT id
        FROM subjects
        WHERE id = ?
    ");

    $check->execute([$subject_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Subject Not Found"
        ]);
        exit;

    }

    // Get Chapters
    $stmt = $pdo->prepare("
        SELECT
            id,
            chapter_name,
            created_at
        FROM chapters
        WHERE subject_id = ?
        ORDER BY id ASC
    ");

    $stmt->execute([$subject_id]);

    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_chapters" => count($chapters),
        "data" => $chapters
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}