<?php
header('Content-Type: application/json');
require_once 'db.php'; // PDO connection

// Enable errors during dev
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🟡 Input decode & basic validation
$data = json_decode(file_get_contents("php://input"), true);
$catId = $data['category_id'] ?? null;
$subId = $data['sub_category_id'] ?? null;

if (!$catId || !$subId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing category_id or sub_category_id']);
    exit;
}

// 🔵 Prepare & execute query
try {
    $sql = "
        SELECT q.id, c.name AS category, s.name AS sub_category, q.question, q.answer
        FROM questions q
        JOIN categories c ON c.id = q.category_id
        JOIN subcategories s ON s.id = q.sub_category_id
        WHERE q.category_id = :cat AND q.sub_category_id = :sub
        ORDER BY q.id ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cat' => $catId, 'sub' => $subId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'DB error',
        'debug' => $e->getMessage()
    ]);
}