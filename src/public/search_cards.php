<?php
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
if (strlen($query) < 3) {
  echo json_encode([]);
  exit();
}

try {
  $stmt = $pdo->prepare("SELECT id, name, image_url FROM Cards WHERE name LIKE :query LIMIT 20");
  $stmt->execute([':query' => '%' . $query . '%']);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($results); // Output JSON
} catch (PDOException $e) {
  error_log($e->getMessage()); // Log the error
  echo json_encode([]); // Return an empty array on failure
}
