<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
  $stmt = $pdo->prepare("SELECT id, username FROM Users WHERE username LIKE :search");
  $stmt->execute([':search' => '%' . $search . '%']);
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Trade Search</title>
</head>

<body>
  <h1>Search for Users to Trade</h1>
  <form method="GET" action="">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search users..." />
    <button type="submit">Search</button>
  </form>

  <?php if ($search): ?>
    <h2>Results</h2>
    <table border="1">
      <tr>
        <th>Username</th>
        <th>Action</th>
      </tr>
      <?php foreach ($users as $user): ?>
        <tr>
          <td><?= htmlspecialchars($user['username']) ?></td>
          <td><a href="trade.php?user_id=<?= $user['id'] ?>">Trade</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <!-- Back to Main Page Button -->
  <br>
  <a href="index.php" style="display: inline-block; padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">Back to Main Page</a>
</body>

</html>