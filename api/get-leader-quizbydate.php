<?php
header("Content-Type: application/json");
require 'db.php';

$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (!$input || !isset($input['date'])) {
    echo json_encode(["status" => "error", "message" => "date required"]);
    exit;
}

$date = $input['date']; // YYYY-MM-DD

try {

    if ($date == date('Y-m-d')) {
        echo json_encode([
            "status" => "error",
            "message" => "Results available only after midnight"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id AS quiz_id, title 
        FROM quizzes 
        WHERE DATE(created_at) = ?
    ");
    $stmt->execute([$date]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "date" => $date,
        "quizzes" => $quizzes
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}