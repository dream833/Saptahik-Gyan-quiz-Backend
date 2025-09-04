<?php
header("Content-Type: application/json");
require 'db.php';

$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (!$input || !isset($input['quizid']) || !isset($input['date'])) {
    echo json_encode(["status" => "error", "message" => "quizid and date required"]);
    exit;
}

$quiz_id = intval($input['quizid']);
$date = $input['date']; // format: YYYY-MM-DD

try {
    // আজকের দিন দিলে leaderboard lock হবে
    if ($date == date('Y-m-d')) {
        echo json_encode([
            "status" => "error",
            "message" => "Leaderboard available only after midnight"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT 
            qa.user_id,
            u.name,
            q.title AS quiz_title,
            qa.score,
            qa.correct_answers,
            (qa.total_questions - qa.correct_answers) AS wrong_answers,
            qa.time_taken,
            qa.attempted_at,
            RANK() OVER (ORDER BY qa.score DESC, qa.time_taken ASC) AS rank
        FROM quiz_attempts qa
        JOIN users u ON qa.user_id = u.id
        JOIN quizzes q ON qa.quiz_id = q.id
        WHERE qa.quiz_id = ? 
          AND qa.attempted_at >= ? 
          AND qa.attempted_at < DATE_ADD(?, INTERVAL 1 DAY)
        ORDER BY qa.score DESC, qa.time_taken ASC
    ");

    $stmt->execute([$quiz_id, $date, $date]);
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "quiz_id" => $quiz_id,
        "quiz_date" => $date,
        "leaderboard" => $leaderboard
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}