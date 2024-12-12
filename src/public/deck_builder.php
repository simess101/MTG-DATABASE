<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deck_name = $_POST['deck_name'];
    $format = $_POST['format'];

    $sql = "INSERT INTO Decks (name, format, user_id) VALUES ('$deck_name', '$format', '$user_id')";
    if ($conn->query($sql) === TRUE) {
        echo "Deck created successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$sql = "SELECT * FROM Decks WHERE user_id = $user_id";
$decks = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deck Builder</title>
</head>
<body>
    <h1>Deck Builder</h1>
    <form method="POST">
        <label for="deck_name">Deck Name:</label>
        <input type="text" id="deck_name" name="deck_name" required>
        <br>
        <label for="format">Format:</label>
        <input type="text" id="format" name="format" required>
        <br>
        <button type="submit">Create Deck</button>
    </form>

    <h2>Your Decks</h2>
    <ul>
        <?php while ($deck = $decks->fetch_assoc()): ?>
            <li><?php echo $deck['name']; ?> (<?php echo $deck['format']; ?>)</li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
