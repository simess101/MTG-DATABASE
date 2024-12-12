<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $deck_id = $_POST['deck_id'];
  $name = $_POST['name'];
  $format = $_POST['format'];
  $commander = $_POST['commander'] ?? null;

  if (empty($deck_id) || empty($name) || empty($format)) {
    die("Error: All fields are required.");
  }

  try {
    $stmt = $pdo->prepare("
            UPDATE Decks
            SET name = :name, format = :format, commander_card_id = :commander
            WHERE id = :deck_id AND user_id = :user_id
        ");
    $stmt->execute([
      ':name' => $name,
      ':format' => $format,
      ':commander' => $commander,
      ':deck_id' => $deck_id,
      ':user_id' => $user_id
    ]);

    header("Location: user_decks.php");
    exit();
  } catch (PDOException $e) {
    die("Error: " . $e->getMessage());
  }
} else {
  die("Invalid request method.");
}
