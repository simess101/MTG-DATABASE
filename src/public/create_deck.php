<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];

try {
  // Fetch all legendary creature cards for the Commander dropdown
  $stmt = $pdo->prepare("SELECT id, name FROM Cards WHERE card_type LIKE '%Legendary Creature%' ORDER BY name ASC");
  $stmt->execute();
  $commanders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Create a Deck</title>
  <script>
    function handleFormatChange() {
      const format = document.getElementById('format').value;
      const commanderField = document.getElementById('commander-select-container');
      if (format === 'Commander' || format === 'EDH') {
        commanderField.style.display = 'block';
      } else {
        commanderField.style.display = 'none';
      }
    }

    function filterCommanders() {
      const input = document.getElementById('commander-search');
      const filter = input.value.toLowerCase();
      const options = document.getElementById('commander-list').getElementsByTagName('li');

      for (let i = 0; i < options.length; i++) {
        const txtValue = options[i].textContent || options[i].innerText;
        if (txtValue.toLowerCase().indexOf(filter) > -1) {
          options[i].style.display = "";
        } else {
          options[i].style.display = "none";
        }
      }
    }

    function selectCommander(id, name) {
      document.getElementById('commander-id').value = id;
      document.getElementById('selected-commander').textContent = name;
      document.getElementById('commander-search').value = ''; // Clear the search
      document.getElementById('commander-list').style.display = 'none'; // Hide the list
    }

    function showCommanderList() {
      document.getElementById('commander-list').style.display = 'block';
    }
  </script>
  <style>
    #commander-list {
      max-height: 200px;
      overflow-y: auto;
      border: 1px solid #ccc;
      padding: 5px;
      margin-top: 5px;
      display: none;
    }

    #commander-list li {
      list-style: none;
      padding: 5px;
      cursor: pointer;
    }

    #commander-list li:hover {
      background-color: #f2f2f2;
    }
  </style>
</head>

<body>
  <h1>Create a New Deck</h1>
  <form action="create_deck_handler.php" method="POST">
    <label for="name">Deck Name:</label>
    <input type="text" id="name" name="name" required><br><br>

    <label for="format">Select Format:</label>
    <select id="format" name="format" onchange="handleFormatChange()" required>
      <option value="Standard">Standard</option>
      <option value="Modern">Modern</option>
      <option value="Legacy">Legacy</option>
      <option value="Commander">Commander</option>
      <option value="EDH">EDH</option>
    </select><br><br>

    <div id="commander-select-container" style="display: none;">
      <label for="commander-search">Search Commander:</label>
      <input type="text" id="commander-search" onkeyup="filterCommanders()" onfocus="showCommanderList()" placeholder="Search for a Commander">
      <input type="hidden" id="commander-id" name="commander">
      <ul id="commander-list">
        <?php foreach ($commanders as $commander): ?>
          <li onclick="selectCommander(<?= $commander['id'] ?>, '<?= htmlspecialchars($commander['name']) ?>')">
            <?= htmlspecialchars($commander['name']) ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p>Selected Commander: <span id="selected-commander">None</span></p>
    </div>

    <button type="submit">Create Deck</button>
  </form>
</body>

</html>