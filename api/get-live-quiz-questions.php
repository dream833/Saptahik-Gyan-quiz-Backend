<?php
header('Content-Type: application/json');
require 'db.php';

$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

// Check quizid
if (!$input || !isset($input['quizid'])) {
    echo json_encode(["status" => "error", "message" => "Quiz ID required"]);
    exit;
}

$quiz_id = intval($input['quizid']);
$today = date("Y-m-d");

try {
    $stmt = $pdo->prepare("SELECT id as quiz_id, title, description, timer, DATE(created_at) as quiz_date 
                           FROM quizzes 
                           WHERE id = ? AND DATE(created_at) = ?");
    $stmt->execute([$quiz_id, $today]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(["status" => "error", "message" => "No quiz found for today"]);
        exit;
    }

    // Force numeric fields to INT
    $quiz = [
        "quiz_id"     => (int) $quiz["quiz_id"],
        "title"       => $quiz["title"],
        "description" => $quiz["description"],
        "timer"       => (int) $quiz["timer"],
        "quiz_date"   => $quiz["quiz_date"]
    ];

    $qstmt = $pdo->prepare("SELECT id as question_id, question, option_a, option_b, option_c, option_d 
                            FROM quiz_questions 
                            WHERE quiz_id = ?");
    $qstmt->execute([$quiz_id]);
    $questions = $qstmt->fetchAll(PDO::FETCH_ASSOC);

    // Force question_id to INT
    $quiz['questions'] = array_map(function($q) {
        return [
            "question_id" => (int) $q["question_id"],
            "question"    => $q["question"],
            "option_a"    => $q["option_a"],
            "option_b"    => $q["option_b"],
            "option_c"    => $q["option_c"],
            "option_d"    => $q["option_d"]
        ];
    }, $questions);

    echo json_encode(["status" => "success", "quiz" => $quiz]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}