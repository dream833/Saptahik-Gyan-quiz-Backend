<?php
header("Content-Type: application/json");
require 'db.php';

$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (!$input || !isset($input['quizid']) || !isset($input['userid']) || !isset($input['answers']) || !isset($input['time_taken'])) {
    echo json_encode(["status" => "error", "message" => "quizid, userid, answers and time_taken required"]);
    exit;
}

$quiz_id = intval($input['quizid']);
$user_id = intval($input['userid']);
$answers = $input['answers']; // array of {question_id, user_answer}
$time_taken = intval($input['time_taken']); // seconds (or minutes)

try {
    // 🔒 Prevent multiple attempts
    $stmt = $pdo->prepare("SELECT id FROM quiz_attempts WHERE quiz_id = ? AND user_id = ?");
    $stmt->execute([$quiz_id, $user_id]);
    $already = $stmt->fetch();
    if ($already) {
        echo json_encode(["status" => "error", "message" => "You have already attempted this quiz"]);
        exit;
    }

    // ✅ Get correct answers
    $qstmt = $pdo->prepare("SELECT id, correct_option FROM quiz_questions WHERE quiz_id = ?");
    $qstmt->execute([$quiz_id]);
    $correctAnswers = $qstmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $total_questions = count($correctAnswers);
    $correct_count = 0;

    // 📝 Insert attempt (time_taken added)
    $insertAttempt = $pdo->prepare("INSERT INTO quiz_attempts 
        (quiz_id, user_id, score, total_questions, correct_answers, time_taken) 
        VALUES (?, ?, 0, ?, 0, ?)");
    $insertAttempt->execute([$quiz_id, $user_id, $total_questions, $time_taken]);
    $attempt_id = $pdo->lastInsertId();

    // 📝 Insert each answer
    $ansInsert = $pdo->prepare("INSERT INTO attempt_answers 
        (attempt_id, question_id, user_answer, correct_option, is_correct) 
        VALUES (?, ?, ?, ?, ?)");

    foreach ($answers as $ans) {
        $qid = intval($ans['question_id']);
        $user_answer = strtoupper(trim($ans['user_answer']));

        $correct_option = isset($correctAnswers[$qid]) ? $correctAnswers[$qid] : null;
        $is_correct = ($user_answer === $correct_option) ? 1 : 0;

        if ($is_correct) $correct_count++;

        $ansInsert->execute([$attempt_id, $qid, $user_answer, $correct_option, $is_correct]);
    }

    // ✅ Update attempt with score
    $updateAttempt = $pdo->prepare("UPDATE quiz_attempts 
                                    SET score = ?, correct_answers = ? 
                                    WHERE id = ?");
    $updateAttempt->execute([$correct_count, $correct_count, $attempt_id]);

    echo json_encode([
        "status" => "success",
        "message" => "Quiz submitted successfully",
        "result" => [
            "quiz_id" => $quiz_id,
            "user_id" => $user_id,
            "total_questions" => $total_questions,
            "correct_answers" => $correct_count,
            "wrong_answers" => $total_questions - $correct_count,
            "score" => $correct_count,
            "time_taken" => $time_taken
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}