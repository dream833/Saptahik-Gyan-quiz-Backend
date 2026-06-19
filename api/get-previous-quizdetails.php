<?php
header('Content-Type: application/json');
require 'db.php';

$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (!$input || !isset($input['user_id']) || !isset($input['quiz_id'])) {
    echo json_encode(["status" => "error", "message" => "user_id and quiz_id required"]);
    exit;
}

$user_id = intval($input['user_id']);
$quiz_id = intval($input['quiz_id']);

try {
    // Get quiz info
    $stmt = $pdo->prepare("SELECT id as quiz_id, title, description, timer, DATE(created_at) as quiz_date 
                           FROM quizzes WHERE id = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(["status" => "error", "message" => "Quiz not found"]);
        exit;
    }

    // Check if user attempted
    $check = $pdo->prepare("SELECT id FROM quiz_attempts WHERE quiz_id = ? AND user_id = ?");
    $check->execute([$quiz_id, $user_id]);
    $attempt = $check->fetch(PDO::FETCH_ASSOC);
    $user_played = $attempt ? true : false;

    // Get questions
    $qstmt = $pdo->prepare("SELECT id as question_id, question, option_a, option_b, option_c, option_d, correct_option 
                            FROM quiz_questions WHERE quiz_id = ?");
    $qstmt->execute([$quiz_id]);
    $questions = $qstmt->fetchAll(PDO::FETCH_ASSOC);

    $formatted = [];
    foreach ($questions as $q) {
        $item = [
            "question_id"   => (string)$q["question_id"],
            "question"      => $q["question"],
            "option_a"      => $q["option_a"],
            "option_b"      => $q["option_b"],
            "option_c"      => $q["option_c"],
            "option_d"      => $q["option_d"],
            "correct_option"=> $q["correct_option"]
        ];

        // If played, fetch user answer
        if ($user_played) {
            $ans = $pdo->prepare("SELECT user_answer, is_correct 
                                  FROM attempt_answers 
                                  WHERE attempt_id = ? AND question_id = ?");
            $ans->execute([$attempt['id'], $q['question_id']]);
            $ansData = $ans->fetch(PDO::FETCH_ASSOC);

            $item["user_answer"] = $ansData ? $ansData["user_answer"] : null;
            $item["is_correct"]  = $ansData ? (bool)$ansData["is_correct"] : null;
        }

        $formatted[] = $item;
    }

    $quizData = [
        "quiz_id"     => (string)$quiz["quiz_id"],
        "title"       => $quiz["title"],
        "description" => $quiz["description"],
        "timer"       => (string)$quiz["timer"],
        "quiz_date"   => $quiz["quiz_date"],
        "user_played" => $user_played,
        "questions"   => $formatted
    ];

    echo json_encode(["status" => "success", "quiz" => $quizData]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}