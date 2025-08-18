<?php
header("Content-Type: application/json");
include "db.php";

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['quiz_id'])) {
        echo json_encode(["status" => "error", "message" => "Quiz ID required"]);
        exit;
    }

    $quiz_id = $data['quiz_id'];

    // Delete quiz (foreign key constraints will delete questions also if ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$quiz_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "success", "message" => "Quiz deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Quiz not found"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}