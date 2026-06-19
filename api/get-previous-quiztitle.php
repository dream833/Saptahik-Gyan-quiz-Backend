<?php
header('Content-Type: application/json');
require 'db.php';

$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (!$input || !isset($input['user_id']) || !isset($input['date'])) {
    echo json_encode(["status" => "error", "message" => "user_id and date required"]);
    exit;
}

$user_id = intval($input['user_id']);
$date = $input['date'];

try {
    // Get quizzes on that date
    $stmt = $pdo->prepare("SELECT id as quiz_id, title, description, timer, DATE(created_at) as quiz_date 
                           FROM quizzes 
                           WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$quizzes) {
        echo json_encode(["status" => "error", "message" => "No quizzes found on this date"]);
        exit;
    }

    $result = [];
    foreach ($quizzes as $quiz) {
        // Check if user played this quiz
        $playedStmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?");
        $playedStmt->execute([$user_id, $quiz['quiz_id']]);
        $played = $playedStmt->fetchColumn() > 0;

        $result[] = [
            "quiz_id"     => (int) $quiz["quiz_id"],
            "title"       => $quiz["title"],
            "description" => $quiz["description"],
            "timer"       => (int) $quiz["timer"],
            "quiz_date"   => $quiz["quiz_date"],
            "played"      => $played
        ];
    }

    echo json_encode(["status" => "success", "quizzes" => $result]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}