<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'db.php'; 


$input = json_decode(file_get_contents("php://input"), true);

if (
    empty($input['category_id']) ||
    empty($input['sub_category_id']) ||
    empty($input['question']) ||
    empty($input['answer'])
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

$category_id = intval($input['category_id']);
$sub_category_id = intval($input['sub_category_id']);
$question = trim($input['question']);
$answer = trim($input['answer']);

try {
    $stmt = $pdo->prepare("
        INSERT INTO questions (category_id, sub_category_id, question, answer)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$category_id, $sub_category_id, $question, $answer]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Question added successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'debug' => $e->getMessage() // Optional: Remove in production
    ]);
}