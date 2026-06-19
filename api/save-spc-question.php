<?php
header("Content-Type: application/json");
require 'db.php'; // DB connection

$input = json_decode(file_get_contents("php://input"), true);

if(!$input || !isset($input['title']) || !isset($input['data'])){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request format"
    ]);
    exit;
}

$title = trim($input['title']);
$data = $input['data'];

if(empty($title) || !is_array($data) || count($data) == 0){
    echo json_encode([
        "status" => "error",
        "message" => "Title or questions missing"
    ]);
    exit;
}

try {
    // 1️⃣ Check if title already exists
    $stmt = $pdo->prepare("SELECT id FROM gk_titles WHERE title = ?");
    $stmt->execute([$title]);
    $titleRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if($titleRow){
        $title_id = $titleRow['id']; // Existing title
    } else {
        // Insert new title
        $stmt = $pdo->prepare("INSERT INTO gk_titles (title) VALUES (?)");
        $stmt->execute([$title]);
        $title_id = $pdo->lastInsertId();
    }

    // 2️⃣ Insert all questions under this title_id
    $stmt = $pdo->prepare("INSERT INTO gk_questions (title_id, question, answer) VALUES (?, ?, ?)");
    foreach($data as $row){
        $q = trim($row['question']);
        $a = trim($row['answer']);
        if(!empty($q) && !empty($a)){
            $stmt->execute([$title_id, $q, $a]);
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Questions saved successfully under '$title'"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}