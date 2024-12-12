<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $format = $_POST['format'];
  $commander = isset($_POST['commander']) ? $_POST['commander'] : null;

  if (empty($name) || empty($format)) {
    die("Error: Deck name and format are required.");
  }

  if (($format === 'Commander' || $format === 'EDH') && !$commander) {
    die("Error: A Commander must be selected for EDH/Commander format.");
  }

  try {
    $stmt = $pdo->prepare("
            INSERT INTO Decks (name, format, commander_card_id, user_id, created_at)
            VALUES (:name, :format, :commander_card_id, :user_id, NOW())
        ");
    $stmt->execute([
      ':name' => $name,
      ':format' => $format,
      ':commander_card_id' => $commander,
      ':user_id' => $user_id
    ]);

    echo "Deck created successfully!";
    header("Location: user_decks.php");
    exit();
  } catch (PDOException $e) {
    die("Error: " . $e->getMessage());
  }
} else {
  echo "Invalid request method.";
}
