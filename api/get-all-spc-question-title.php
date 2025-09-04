<?php
header("Content-Type: application/json");
require 'db.php';

try {
    $stmt = $pdo->query("SELECT id, title FROM gk_titles ORDER BY id DESC");
    $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "titles" => $titles
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}