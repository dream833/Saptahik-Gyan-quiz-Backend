<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'db.php'; // Ensure your DB connection is here

// Read and decode JSON input
$input = json_decode(file_get_contents('php://input'), true);
$question_id = isset($input['question_id']) ? intval($input['question_id']) : 0;

// Validate
if (!$question_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing question_id'
    ]);
    exit;
}

try {
    // Check if the question exists
    $check = $pdo->prepare("SELECT id FROM questions WHERE id = ?");
    $check->execute([$question_id]);

    if ($check->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Question not found'
        ]);
        exit;
    }

    // Delete the question
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Question deleted successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'debug' => $e->getMessage() // Optional: remove in production
    ]);
}