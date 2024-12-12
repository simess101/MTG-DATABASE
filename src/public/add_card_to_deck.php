<?php
session_start();
require_once '../includes/db_connect.php';

// Validate the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
  exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User is not logged in.']);
  exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$deck_id = $data['deck_id'] ?? null;
$card_id = $data['card_id'] ?? null;

if (!$deck_id || !$card_id) {
  echo json_encode(['success' => false, 'message' => 'Invalid deck or card ID.']);
  exit();
}

try {
  // Check if the card is already in the deck
  $stmt = $pdo->prepare("SELECT quantity FROM Deck_Cards WHERE deck_id = :deck_id AND card_id = :card_id");
  $stmt->execute([':deck_id' => $deck_id, ':card_id' => $card_id]);
  $existing_card = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_card) {
    // Update the card's quantity
    $stmt = $pdo->prepare("UPDATE Deck_Cards SET quantity = quantity + 1 WHERE deck_id = :deck_id AND card_id = :card_id");
    $stmt->execute([':deck_id' => $deck_id, ':card_id' => $card_id]);
  } else {
    // Insert the card into the deck
    $stmt = $pdo->prepare("INSERT INTO Deck_Cards (deck_id, card_id, quantity) VALUES (:deck_id, :card_id, 1)");
    $stmt->execute([':deck_id' => $deck_id, ':card_id' => $card_id]);
  }

  echo json_encode(['success' => true, 'message' => 'Card added successfully.']);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
