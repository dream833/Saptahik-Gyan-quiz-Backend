<?php
header("Content-Type: application/json");
include "db.php";

try {
    if (empty($_GET['date'])) {
        echo json_encode(["status" => "error", "message" => "Date is required"]);
        exit;
    }

    $date = $_GET['date']; // format YYYY-MM-DD

    // shudhu oi diner quiz id nibo
    $stmt = $pdo->prepare("
        SELECT qa.id, u.first_name, u.last_name, qa.score, qa.correct_answers, 
               qa.total_questions, qa.time_taken, qa.attempted_at
        FROM quiz_attempts qa
        JOIN users u ON qa.user_id = u.id
        JOIN quizzes q ON qa.quiz_id = q.id
        WHERE DATE(q.quiz_date) = ?
        ORDER BY qa.score DESC, qa.time_taken ASC, qa.attempted_at ASC
    ");
    $stmt->execute([$date]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "leaderboard" => $rows
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>