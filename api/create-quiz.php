<?php
header("Content-Type: application/json");
include "db.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);
try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !$data || 
        empty($data['title']) || 
        empty($data['description']) || 
        empty($data['timer']) || 
        empty($data['questions']) || 
        !is_array($data['questions'])
    ) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

  
    $today = date("Y-m-d");

   
    $stmt = $pdo->prepare("INSERT INTO quizzes (title, description, timer, quiz_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['title'], $data['description'], $data['timer'], $today]);

    $quiz_id = $pdo->lastInsertId();


    $stmtQ = $pdo->prepare("INSERT INTO quiz_questions 
        (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($data['questions'] as $q) {
        if (
            empty($q['question']) ||
            empty($q['option_a']) ||
            empty($q['option_b']) ||
            empty($q['option_c']) ||
            empty($q['option_d']) ||
            empty($q['correct_option'])
        ) {
            echo json_encode(["status" => "error", "message" => "Each question must have text, 4 options and correct answer"]);
            exit;
        }

        $stmtQ->execute([
            $quiz_id,
            $q['question'],
            $q['option_a'],
            $q['option_b'],
            $q['option_c'],
            $q['option_d'],
            strtoupper($q['correct_option']) // force A/B/C/D
        ]);
    }

    echo json_encode([
        "status" => "success", 
        "message" => "Quiz created successfully", 
        "quiz_id" => $quiz_id
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}