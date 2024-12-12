<?php
set_time_limit(0); // Allow unlimited execution time
require_once '../includes/db_connect.php'; // Include your database connection

function fetchCardsFromMTG($page = 1)
{
    $url = "https://api.magicthegathering.io/v1/cards?page=$page";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL error: " . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    return $data['cards'] ?? [];
}

function saveCardToDatabase($pdo, $card)
{
    $art_variation = isset($card['printings']) ? implode(', ', $card['printings']) : null;
    try {
        // Check if the card already exists (using name, set_name, and art_variation as a unique identifier)
        $stmt = $pdo->prepare("SELECT id FROM Cards WHERE name = :name AND set_name = :set_name AND art_variation = :art_variation");
        $stmt->execute([
            ':name' => $card['name'],
            ':set_name' => $card['set'] ?? null, // The "set" field from the API
            ':art_variation' => implode(", ", $card['printings'] ?? []) // Convert printings array to string
        ]);

        if ($stmt->rowCount() > 0) {
            // Card already exists, skip insertion
            return;
        }

        // Insert the card into the database
        $stmt = $pdo->prepare("
            INSERT INTO Cards (name, mana_cost, card_type, rarity, set_name, rules_text, art_variation, image_url) 
            VALUES (:name, :mana_cost, :card_type, :rarity, :set_name, :rules_text, :art_variation, :image_url)
        ");
        $stmt->execute([
            ':name' => $card['name'],
            ':mana_cost' => $card['manaCost'] ?? null,
            ':card_type' => $card['type'] ?? null,
            ':rarity' => $card['rarity'] ?? null,
            ':set_name' => $card['set'] ?? null,
            ':rules_text' => $card['text'] ?? null,
            ':art_variation' => $art_variation,
            ':image_url' => $card['imageUrl'] ?? null // The "imageUrl" field from the API
        ]);
    } catch (PDOException $e) {
        echo "Failed to save card: " . $e->getMessage();
    }
}

// Fetch and save all cards
$page = 1;
do {
    echo "Fetching page $page...<br>";
    $Cards = fetchCardsFromMTG($page);

    if (empty($Cards)) {
        echo "No more cards found.<br>";
        break;
    }

    foreach ($Cards as $card) {
        saveCardToDatabase($pdo, $card);
    }

    $page++;
} while (!empty($Cards));

echo "All cards saved to the database.";
