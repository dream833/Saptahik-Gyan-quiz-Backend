<?php
header("Content-Type: application/json");
require 'db.php';

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['title_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "title_id required"
    ]);
    exit;
}

$title_id = intval($input['title_id']);

try {
    // Title Info
    $stmt = $pdo->prepare("SELECT id, title FROM gk_titles WHERE id = ?");
    $stmt->execute([$title_id]);
    $title = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$title) {
        echo json_encode([
            "status" => "error",
            "message" => "Title not found"
        ]);
        exit;
    }


    $stmt = $pdo->prepare("SELECT id, question, answer FROM gk_questions WHERE title_id = ?");
    $stmt->execute([$title_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "title" => $title,
        "questions" => $questions
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}