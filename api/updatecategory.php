<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

include 'db.php'; 
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$id = $data['id'];
$name = trim($data['name']);

if ($name === "") {
    echo json_encode(['status' => 'error', 'message' => 'Name cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);

    echo json_encode(['status' => 'success', 'message' => 'Category updated']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}
?>