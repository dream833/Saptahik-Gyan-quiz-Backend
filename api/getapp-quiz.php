<?php
header("Content-Type: application/json");
require 'db.php';

try {
    // আজকের দিনের শুরু আর শেষ সময় বের করলাম (server time অনুযায়ী)
    $startOfDay = date("Y-m-d 00:00:00");
    $endOfDay   = date("Y-m-d 23:59:59");

    // আজকের দিনের সব quiz আনবো
    $stmt = $pdo->prepare("SELECT id as quiz_id, title, description, timer, quiz_date 
                           FROM quizzes 
                           WHERE quiz_date BETWEEN ? AND ?
                           ORDER BY id DESC");
    $stmt->execute([$startOfDay, $endOfDay]);
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