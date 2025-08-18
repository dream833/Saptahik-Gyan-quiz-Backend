<?php
header('Content-Type: application/json');
require_once 'db.php'; // Adjust path if needed


$data = json_decode(file_get_contents('php://input'), true);


if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing subcategory ID'
    ]);
    exit;
}

$subcategoryId = intval($data['id']);

try {
    $stmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
    $stmt->execute([$subcategoryId]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Subcategory deleted successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
}