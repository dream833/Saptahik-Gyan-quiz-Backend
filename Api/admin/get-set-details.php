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

    // Get Sets with Total Questions
    $stmt = $pdo->prepare("
        SELECT
            s.id,
            s.set_name,
            s.duration_minutes,
            COUNT(a.id) AS total_questions
        FROM sets s
        LEFT JOIN all_mock_tests a
            ON a.set_id = s.id
        WHERE s.chapter_id = ?
        GROUP BY
            s.id,
            s.set_name,
            s.duration_minutes
        ORDER BY s.id ASC
    ");

    $stmt->execute([$chapter_id]);

    $sets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_sets" => count($sets),
        "data" => $sets
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}