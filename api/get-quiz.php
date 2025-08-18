<?php
header("Content-Type: application/json");
include "db.php";

try {
    if (empty($_GET['date'])) {
        echo json_encode(["status" => "error", "message" => "Date is required"]);
        exit;
    }

    $date = $_GET['date'];

    $stmt = $pdo->prepare("
        SELECT q.id as quiz_id, q.title, q.description, q.timer, q.quiz_date,
               qq.id as question_id, qq.question, qq.option_a, qq.option_b, qq.option_c, qq.option_d, qq.correct_option
        FROM quizzes q
        LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
        WHERE q.quiz_date = ?
        ORDER BY q.id DESC, qq.id ASC
    ");
    $stmt->execute([$date]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo json_encode(["status" => "success", "quizzes" => []]);
        exit;
    }

    $result = [];
    foreach ($rows as $row) {
        $quiz_id = $row['quiz_id'];

        if (!isset($result[$quiz_id])) {
            $result[$quiz_id] = [
                "id" => $quiz_id,  // 🔑 id instead of quiz_id
                "title" => $row['title'],
                "description" => $row['description'],
                "timer" => $row['timer'],
                "quiz_date" => $row['quiz_date'],
                "questions" => []
            ];
        }

        if (!empty($row['question'])) {
            $result[$quiz_id]["questions"][] = [
                "id" => $row['question_id'],   // same, id instead of question_id
                "question" => $row['question'],
                "option_a" => $row['option_a'],
                "option_b" => $row['option_b'],
                "option_c" => $row['option_c'],
                "option_d" => $row['option_d'],
                "correct_option" => $row['correct_option']
            ];
        }
    }

    echo json_encode([
        "status" => "success",
        "quizzes" => array_values($result)
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}