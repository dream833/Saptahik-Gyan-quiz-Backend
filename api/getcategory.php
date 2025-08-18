<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 

include 'db.php';

try {

    $stmt = $pdo->query("SELECT id, name, image, created_at FROM categories ORDER BY id ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    echo json_encode([
        'status' => 'success',
        'data' => $categories
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Something went wrong while fetching categories.',
        'error' => $e->getMessage()
    ]);
}
?>