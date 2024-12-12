<?php
require_once '../includes/db_connect.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Handle form submission to add cards
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
  $card_name = $_POST['card_name'] ?? null;
  $quantity = $_POST['quantity'] ?? null;

  if ($card_name && $quantity && $quantity > 0) {
    try {
      // Search for the card by name
      $stmt = $pdo->prepare("SELECT id, image_url FROM Cards WHERE name = :name");
      $stmt->execute([':name' => $card_name]);
      $card = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($card) {
        $card_id = $card['id'];

        // Check if the card is already in the user's collection
        $stmt = $pdo->prepare("SELECT id FROM Collection WHERE user_id = :user_id AND card_id = :card_id");
        $stmt->execute([
          ':user_id' => $user_id,
          ':card_id' => $card_id
        ]);
        $existing_collection = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_collection) {
          // Update the quantity if the card already exists in the collection
          $stmt = $pdo->prepare("UPDATE Collection SET quantity = quantity + :quantity WHERE id = :id");
          $stmt->execute([
            ':quantity' => $quantity,
            ':id' => $existing_collection['id']
          ]);
          $success_message = "Card quantity updated successfully!";
        } else {
          // Insert a new card into the collection
          $stmt = $pdo->prepare("INSERT INTO Collection (user_id, card_id, quantity) VALUES (:user_id, :card_id, :quantity)");
          $stmt->execute([
            ':user_id' => $user_id,
            ':card_id' => $card_id,
            ':quantity' => $quantity
          ]);
          $success_message = "Card added to your collection!";
        }
      } else {
        $error_message = "Card not found.";
      }
    } catch (PDOException $e) {
      $error_message = "Error: " . $e->getMessage();
    }
  } else {
    $error_message = "Please enter a valid card name and quantity.";
  }
}

// Handle form submission to remove cards
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
  $collection_id = $_POST['collection_id'] ?? null;

  if ($collection_id) {
    try {
      // Remove the card from the user's collection
      $stmt = $pdo->prepare("DELETE FROM Collection WHERE id = :id AND user_id = :user_id");
      $stmt->execute([
        ':id' => $collection_id,
        ':user_id' => $user_id
      ]);

      $success_message = "Card removed from your collection!";
    } catch (PDOException $e) {
      $error_message = "Error: " . $e->getMessage();
    }
  } else {
    $error_message = "Invalid card selected.";
  }
}

try {
  // Fetch the user's collection, including the card images
  $stmt = $pdo->prepare("SELECT c.id AS collection_id, ca.name, c.quantity, ca.image_url 
                           FROM Collection c 
                           JOIN Cards ca ON c.card_id = ca.id 
                           WHERE c.user_id = :user_id");
  $stmt->execute([':user_id' => $user_id]);
  $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User Collections</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

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

    img {
      max-width: 100px;
      max-height: 140px;
      cursor: pointer;
    }

    .form-group {
      margin: 10px 0;
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.8);
    }

    .modal-content {
      margin: 10% auto;
      padding: 20px;
      width: 80%;
      text-align: center;
    }

    .modal-content img {
      max-width: 100%;
      max-height: 90vh;
    }

    .close {
      color: white;
      font-size: 28px;
      font-weight: bold;
      position: absolute;
      right: 20px;
      top: 20px;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <h1>Your Card Collections</h1>

  <?php if ($error_message): ?>
    <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
  <?php endif; ?>

  <?php if ($success_message): ?>
    <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
  <?php endif; ?>

  <table>
    <tr>
      <th>Card Name</th>
      <th>Image</th>
      <th>Quantity</th>
      <th>Action</th>
    </tr>
    <?php foreach ($collections as $collection): ?>
      <tr>
        <td><?= htmlspecialchars($collection['name']) ?></td>
        <td>
          <?php if ($collection['image_url']): ?>
            <img src="<?= htmlspecialchars($collection['image_url']) ?>" alt="<?= htmlspecialchars($collection['name']) ?>" class="card-image">
          <?php else: ?>
            No Image Available
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($collection['quantity']) ?></td>
        <td>
          <!-- Form to remove card -->
          <form method="POST" action="" style="display: inline;">
            <input type="hidden" name="collection_id" value="<?= htmlspecialchars($collection['collection_id']) ?>">
            <input type="hidden" name="action" value="remove">
            <button type="submit">Remove</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Add Cards to Your Collection</h2>
  <form method="POST" action="">
    <div class="form-group">
      <label for="card_name">Search for Card:</label>
      <input type="text" name="card_name" id="card_name" placeholder="Enter card name" required>
    </div>
    <div class="form-group">
      <label for="quantity">Quantity:</label>
      <select name="quantity" id="quantity" required>
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <input type="hidden" name="action" value="add">
    <button type="submit">Add to Collection</button>
  </form>

  <br>
  <a href="index.php">Back to Main Page</a>

  <!-- Modal structure -->
  <div id="imageModal" class="modal">
    <span class="close">&times;</span>
    <div class="modal-content">
      <img id="modalImage" src="" alt="Card Image">
    </div>
  </div>

  <script>
    // Modal functionality
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeModal = document.querySelector('.close');

    document.querySelectorAll('.card-image').forEach(image => {
      image.addEventListener('click', function() {
        modalImage.src = this.src;
        modal.style.display = 'block';
      });
    });

    closeModal.addEventListener('click', function() {
      modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    });
  </script>
</body>

</html>