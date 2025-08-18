<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'db.php'; 


$input = json_decode(file_get_contents("php://input"), true);
$category_id = isset($input['category_id']) ? intval($input['category_id']) : null;

if (!$category_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing category_id'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name FROM subcategories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        // 'debug' => $e->getMessage()
    ]);
}