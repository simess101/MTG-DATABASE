<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$deck_id = $_GET['deck_id'] ?? null;

if (!$deck_id) {
  die("Error: Deck ID not provided.");
}

try {
  // Fetch deck details
  $stmt = $pdo->prepare("SELECT name, format, commander_card_id FROM Decks WHERE id = :deck_id AND user_id = :user_id");
  $stmt->execute([':deck_id' => $deck_id, ':user_id' => $user_id]);
  $deck = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$deck) {
    die("Error: Deck not found.");
  }

  // Ensure the commander is in the deck for Commander format
  if ($deck['commander_card_id']) {
    $stmt = $pdo->prepare("
            INSERT INTO Deck_Cards (deck_id, card_id, quantity)
            SELECT :deck_id, :commander_id, 1
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1 FROM Deck_Cards WHERE deck_id = :deck_id AND card_id = :commander_id
            )
        ");
    $stmt->execute([
      ':deck_id' => $deck_id,
      ':commander_id' => $deck['commander_card_id']
    ]);
  }

  // Fetch all cards in the deck, grouped by card type
  $stmt = $pdo->prepare("
        SELECT 
            c.id AS card_id,
            c.name AS card_name,
            c.image_url,
            c.card_type,
            c.mana_cost,
            c.rarity,
            c.set_name,
            dc.quantity
        FROM Deck_Cards dc
        JOIN Cards c ON dc.card_id = c.id
        WHERE dc.deck_id = :deck_id
        ORDER BY 
            CASE 
                WHEN c.card_type = 'Commander' THEN 0
                WHEN c.card_type = 'Creature' THEN 1
                WHEN c.card_type = 'Land' THEN 2
                WHEN c.card_type = 'Artifact' THEN 3
                WHEN c.card_type = 'Enchantment' THEN 4
                WHEN c.card_type = 'Planeswalker' THEN 5
                WHEN c.card_type = 'Instant' THEN 6
                WHEN c.card_type = 'Sorcery' THEN 7
                ELSE 8
            END, 
            c.name
    ");
  $stmt->execute([':deck_id' => $deck_id]);
  $deck_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Define card type categories
  $card_types = [
    "Commander" => [],
    "Creature" => [],
    "Land" => [],
    "Artifact" => [],
    "Enchantment" => [],
    "Planeswalker" => [],
    "Instant" => [],
    "Sorcery" => [],
    "Other" => []
  ];

  foreach ($deck_cards as $card) {
    if ($card['card_id'] == $deck['commander_card_id']) {
      $card_types['Commander'][] = $card;
    } elseif (isset($card_types[$card['card_type']])) {
      $card_types[$card['card_type']][] = $card;
    } else {
      $card_types['Other'][] = $card;
    }
  }
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Deck: <?= htmlspecialchars($deck['name']) ?></title>
  <style>
    .nav-buttons {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 20px;
    }

    .nav-buttons a {
      margin-left: 10px;
      padding: 10px 15px;
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }

    .nav-buttons a:hover {
      background-color: #0056b3;
    }

    .card-type-section {
      margin-bottom: 20px;
    }

    .card {
      display: inline-block;
      text-align: center;
      margin: 10px;
    }

    .card img {
      max-width: 100px;
      max-height: 140px;
    }

    .remove-button {
      background-color: #FF4136;
      margin-top: 5px;
      padding: 5px;
      border: none;
      border-radius: 5px;
      color: white;
      cursor: pointer;
    }

    .remove-button:hover {
      background-color: #E3342F;
    }

    #editDeckModal {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 1000;
      background: white;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }

    #modalOverlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }

    #commander-list {
      border: 1px solid #ccc;
      max-height: 200px;
      overflow-y: auto;
      display: none;
      list-style: none;
      padding: 0;
      margin: 0;
      background: white;
      position: absolute;
      z-index: 1001;
      width: 100%;
    }

    #commander-list li {
      padding: 8px 10px;
      cursor: pointer;
    }

    #commander-list li:hover {
      background-color: #f0f0f0;
    }
  </style>
  <script>
    function removeCardFromDeck(cardId, cardName) {
      fetch(`remove_card_from_deck.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            deck_id: <?= $deck_id ?>,
            card_id: cardId,
          }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(`${cardName} removed from deck.`);
            location.reload(); // Reload the page to reflect changes
          } else {
            alert('Error removing card from deck.');
          }
        })
        .catch(error => {
          console.error('Error removing card from deck:', error);
        });
    }

    function openEditDeckModal() {
      document.getElementById('editDeckModal').style.display = 'block';
      document.getElementById('modalOverlay').style.display = 'block';
    }

    function closeEditDeckModal() {
      document.getElementById('editDeckModal').style.display = 'none';
      document.getElementById('modalOverlay').style.display = 'none';
    }

    function saveDeckChanges() {
      const deckName = document.getElementById('deckName')?.value;
      const commanderId = document.getElementById('commander-id')?.value;

      if (!deckName || !commanderId) {
        alert('Both deck name and commander must be selected.');
        return;
      }

      fetch('update_deck.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            deck_id: <?= $deck_id ?>,
            deck_name: deckName,
            commander_id: commanderId,
          }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Deck updated successfully.');
            location.reload(); // Reload the page to reflect changes
          } else {
            alert('Error updating deck: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error updating deck:', error);
          alert('An error occurred while updating the deck.');
        });
    }


    function addCardToDeck(cardId, cardName) {
      fetch(`add_card_to_deck.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            deck_id: <?= $deck_id ?>,
            card_id: cardId,
          }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(`${cardName} added to deck.`);
            location.reload(); // Reload the page to reflect changes
          } else {
            alert('Error adding card to deck.');
          }
        })
        .catch(error => {
          console.error('Error adding card to deck:', error);
        });
    }

    function filterCommanders() {
      const query = document.getElementById('commander-search').value.toLowerCase();
      const commanderList = document.getElementById('commander-list');
      const commanders = commanderList.getElementsByTagName('li');

      console.log('Search Query:', query);
      console.log('Total Commanders:', commanders.length); // Debugging

      for (let commander of commanders) {
        if (commander.textContent.toLowerCase().includes(query)) {
          commander.style.display = 'block';
        } else {
          commander.style.display = 'none';
        }
      }
    }



    function showCommanderList() {
      document.getElementById('commander-list').style.display = 'block';
    }


    function selectCommander(id, name) {
      document.getElementById('commander-id').value = id;
      document.getElementById('selected-commander').textContent = name;
      document.getElementById('commander-list').style.display = 'none';
    }

    function autocompleteSearch() {
      const query = document.getElementById('search-bar').value;

      if (query.length < 3) {
        document.getElementById('autocomplete-results').innerHTML = ''; // Clear results
        return;
      }

      fetch(`search_cards.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          const resultsContainer = document.getElementById('autocomplete-results');
          resultsContainer.innerHTML = ''; // Clear previous results

          if (data.length === 0) {
            resultsContainer.innerHTML = '<li>No results found</li>';
            return;
          }

          data.forEach(card => {
            const resultItem = document.createElement('li');
            resultItem.innerHTML = `
                            <img src="${card.image_url || '/path/to/default_image.jpg'}" alt="${card.name}" style="width: 50px; height: 70px; margin-right: 10px;">
                            <span>${card.name}</span>
                            <button onclick="addCardToDeck(${card.id}, '${card.name}')">Add</button>
                        `;
            resultsContainer.appendChild(resultItem);
          });
        })
        .catch(error => {
          console.error('Error fetching cards:', error);
        });
    }
  </script>
</head>

<body>
  <div class="nav-buttons">
    <button onclick="openEditDeckModal()">Edit Deck</button>
    <a href="user_decks.php">Back to Decks</a>
    <a href="index.php">Back to Main</a>
  </div>

  <h1>Edit Deck: <?= htmlspecialchars($deck['name']) ?></h1>

  <?php foreach ($card_types as $type => $cards): ?>
    <?php if (!empty($cards)): ?>
      <div class="card-type-section">
        <h3><?= htmlspecialchars($type) ?></h3>
        <?php foreach ($cards as $card): ?>
          <div class="card">
            <img src="<?= htmlspecialchars($card['image_url']) ?>" alt="<?= htmlspecialchars($card['card_name']) ?>" />
            <p><?= htmlspecialchars($card['card_name']) ?> (x<?= htmlspecialchars($card['quantity']) ?>)</p>
            <button class="remove-button" onclick="removeCardFromDeck(<?= $card['card_id'] ?>, '<?= htmlspecialchars($card['card_name']) ?>')">Remove</button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>




  <h2>Add Cards</h2>
  <div style="position: relative;">
    <input type="text" id="search-bar" oninput="autocompleteSearch()" placeholder="Search for cards...">
    <ul id="autocomplete-results" class="autocomplete-results"></ul>
  </div>

  <!-- Modal for Editing Deck Name and Commander -->
  <div id="editDeckModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; background: white; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2>Edit Deck</h2>
    <form id="editDeckForm">
      <label for="deckName">Deck Name:</label><br>
      <input type="text" id="deckName" name="deckName" value="<?= htmlspecialchars($deck['name']) ?>" required><br><br>

      <div id="commander-select-container">
        <label for="commander-search">Search Commander:</label><br>
        <input type="text" id="commander-search" onkeyup="filterCommanders()" onfocus="showCommanderList()" placeholder="Search for a Commander">
        <input type="hidden" id="commander-id" name="commander" value="<?= htmlspecialchars($deck['commander_card_id']) ?>">
        <ul id="commander-list">
          <?php foreach ($commanders as $commander): ?>
            <li onclick="selectCommander(<?= $commander['id'] ?>, '<?= htmlspecialchars($commander['name']) ?>')">
              <?= htmlspecialchars($commander['name']) ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <?php
        // Fetch all possible commanders
        $stmt = $pdo->prepare("SELECT id, name FROM Cards WHERE card_type LIKE '%Commander%'");
        $stmt->execute();
        $commanders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($commanders)) {
          die("No commanders found in the database. Please check the Cards table.");
        }

        foreach ($commanders as $commander):
        ?>
          <li onclick="selectCommander(<?= $commander['id'] ?>, '<?= htmlspecialchars($commander['name']) ?>')">
            <?= htmlspecialchars($commander['name']) ?>
          </li>
        <?php endforeach; ?>
        </ul>
        <p>Selected Commander: <span id="selected-commander">
            <?php
            $selectedCommanderName = array_reduce($commanders, function ($carry, $item) use ($deck) {
              return $item['id'] == $deck['commander_card_id'] ? $item['name'] : $carry;
            }, "None");
            echo htmlspecialchars($selectedCommanderName);
            ?>
          </span></p>
      </div><br><br>

      <button type="button" onclick="saveDeckChanges()">Save Changes</button>
      <button type="button" onclick="closeEditDeckModal()">Cancel</button>
    </form>
  </div>
  <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999;" onclick="closeEditDeckModal()"></div>


</body>

</html>