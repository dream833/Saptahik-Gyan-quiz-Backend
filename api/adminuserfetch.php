<?php

header('Content-Type: application/json');
require 'db.php'; 

try {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, created_at FROM users ORDER BY id ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["users" => $users]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}