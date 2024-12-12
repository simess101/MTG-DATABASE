<?php
require_once '../includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $deck_id = $input['deck_id'] ?? null;
  $card_id = $input['card_id'] ?? null;

  if (!$deck_id || !$card_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit();
  }

  try {
    $stmt = $pdo->prepare("DELETE FROM Deck_Cards WHERE deck_id = :deck_id AND card_id = :card_id");
    $stmt->execute([':deck_id' => $deck_id, ':card_id' => $card_id]);

    echo json_encode(['success' => true]);
  } catch (PDOException $e) {
    error_log("Error removing card: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
  }
}
