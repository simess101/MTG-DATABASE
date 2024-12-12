<?php
// Database connection
require_once '../includes/db_connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
// Add a mana cost key for users
echo "<h3>Mana Cost Key</h3>";
echo "<table border='1' style='width: 50%; text-align: left;'>";
echo "<tr><th>Symbol</th><th>Meaning</th></tr>";
echo "<tr><td>{W}</td><td>White Mana</td></tr>";
echo "<tr><td>{U}</td><td>Blue Mana</td></tr>";
echo "<tr><td>{B}</td><td>Black Mana</td></tr>";
echo "<tr><td>{R}</td><td>Red Mana</td></tr>";
echo "<tr><td>{G}</td><td>Green Mana</td></tr>";
echo "<tr><td>{C}</td><td>Colorless Mana</td></tr>";
echo "<tr><td>{X}</td><td>Variable Mana Cost</td></tr>";
echo "</table>";

echo "<h1>Search Cards</h1>";
echo '<form method="GET" action="">
        <input type="text" name="search" value="' . htmlspecialchars($search) . '" />
        <button type="submit">Search</button>
      </form>';


if ($search) {
    try {
        // Fetch cards matching the search term
        $stmt = $pdo->prepare("SELECT name, card_type, mana_cost, rules_text, art_variation FROM Cards WHERE name LIKE :search");
        $stmt->execute([':search' => '%' . $search . '%']);
        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($cards) {
            echo "<h2>Search Results</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Name</th><th>Type</th><th>Mana Cost</th><th>Description</th><th>Art Variation</th></tr>";

            foreach ($cards as $card) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($card['name']) . "</td>";
                echo "<td>" . htmlspecialchars($card['card_type'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($card['mana_cost'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($card['rules_text'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($card['art_variation'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "<p>No results found for \"" . htmlspecialchars($search) . "\"</p>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
