<?php
header("Content-Type: application/json");
require 'db.php';

try {
   
    $inputJSON = file_get_contents("php://input");
    $input = json_decode($inputJSON, true);

    if (!isset($input['user_id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "User ID is required"
        ]);
        exit;
    }

    $user_id = intval($input['user_id']);

    // আজকের দিনের শুরু আর শেষ সময়
    $startOfDay = date("Y-m-d 00:00:00");
    $endOfDay   = date("Y-m-d 23:59:59");

    // আজকের কুইজগুলো আনবো + check করব attempt হয়েছে কিনা
    $stmt = $pdo->prepare("
        SELECT q.id as quiz_id, q.title, q.description, q.timer, q.quiz_date,
               CASE WHEN qa.id IS NOT NULL THEN 1 ELSE 0 END as attempted
        FROM quizzes q
        LEFT JOIN quiz_attempts qa 
               ON qa.quiz_id = q.id AND qa.user_id = ?
        WHERE q.quiz_date BETWEEN ? AND ?
        ORDER BY q.id DESC
    ");
    $stmt->execute([$user_id, $startOfDay, $endOfDay]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($quizzes)) {
        echo json_encode([
            "status" => "success",
            "message" => "No quiz available for today",
            "quizzes" => []
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "quizzes" => $quizzes
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}