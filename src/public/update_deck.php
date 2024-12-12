<?php
require_once '../includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $deck_id = $input['deck_id'] ?? null;
  $deck_name = $input['deck_name'] ?? null;
  $commander_id = $input['commander_id'] ?? null;

  if (!$deck_id || !$deck_name || !$commander_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit();
  }

  try {
    // Update the deck name and commander
    $stmt = $pdo->prepare("UPDATE Decks SET name = :deck_name, commander_card_id = :commander_id WHERE id = :deck_id AND user_id = :user_id");
    $stmt->execute([
      ':deck_name' => $deck_name,
      ':commander_id' => $commander_id,
      ':deck_id' => $deck_id,
      ':user_id' => $_SESSION['user_id']
    ]);

    echo json_encode(['success' => true]);
  } catch (PDOException $e) {
    error_log("Error updating deck: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
  }
}
