<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];

try {
  // Fetch the user's decks, including the Commander's name if available
  $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.format, d.created_at, c.name AS commander_name
        FROM Decks d
        LEFT JOIN Cards c ON d.commander_card_id = c.id
        WHERE d.user_id = :user_id
        ORDER BY d.created_at DESC
    ");
  $stmt->execute([':user_id' => $user_id]);
  $decks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Your Decks</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    th,
    td {
      border: 1px solid black;
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
    }

    .button {
      padding: 10px 15px;
      background-color: #4CAF50;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      display: inline-block;
      margin-bottom: 20px;
    }

    .button:hover {
      background-color: #45a049;
    }

    .empty-message {
      text-align: center;
      margin-top: 20px;
      font-size: 1.2em;
      color: #555;
    }
  </style>
</head>

<body>
  <h1>Your Decks</h1>
  <a href="create_deck.php" class="button">Create New Deck</a>

  <?php if (empty($decks)): ?>
    <p class="empty-message">You have not created any decks yet.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>Deck Name</th>
        <th>Format</th>
        <th>Commander</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($decks as $deck): ?>
        <tr>
          <td><?= htmlspecialchars($deck['name']) ?></td>
          <td><?= htmlspecialchars($deck['format']) ?></td>
          <td><?= $deck['commander_name'] ? htmlspecialchars($deck['commander_name']) : 'N/A' ?></td>
          <td><?= htmlspecialchars($deck['created_at']) ?></td>
          <td>
            <a href="edit_deck.php?deck_id=<?= $deck['id'] ?>" class="button">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</body>

</html>