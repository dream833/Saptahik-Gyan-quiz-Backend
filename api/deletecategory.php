<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require 'db.php'; // Ensure this connects you to the $pdo object

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
  echo json_encode([
    "status" => "error",
    "message" => "Invalid or missing category ID"
  ]);
  exit;
}

$categoryId = intval($data['id']);

try {
  // Begin transaction to ensure all-or-nothing deletion
  $pdo->beginTransaction();

  // Step 1: Get all subcategory IDs under this category
  $stmt = $pdo->prepare("SELECT id FROM subcategories WHERE category_id = :category_id");
  $stmt->execute(['category_id' => $categoryId]);
  $subcategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

  // Step 2: Delete questions under those subcategories (if any)
  if (!empty($subcategories)) {
    $placeholders = implode(',', array_fill(0, count($subcategories), '?'));
    $stmt = $pdo->prepare("DELETE FROM questions WHERE sub_category_id IN ($placeholders)");
    $stmt->execute($subcategories);
  }

  // Step 3: Delete subcategories under the category
  $stmt = $pdo->prepare("DELETE FROM subcategories WHERE category_id = :category_id");
  $stmt->execute(['category_id' => $categoryId]);

  // Step 4: Delete the category itself
  $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
  $stmt->execute(['id' => $categoryId]);

  // Commit all changes
  $pdo->commit();

  echo json_encode([
    "status" => "success",
    "message" => "Category and related subcategories and questions deleted successfully"
  ]);

} catch (PDOException $e) {
  $pdo->rollBack();
  echo json_encode([
    "status" => "error",
    "message" => "Database error. Could not delete category.",
    // For debugging only; remove 'error' key in production
    "error" => $e->getMessage()
  ]);
}