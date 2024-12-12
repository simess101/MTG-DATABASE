<?php
require_once '../includes/db_connect.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$trade_with_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if (!$trade_with_user_id) {
    echo "Invalid user for trade.";
    exit();
}

try {
    // Fetch the other user's collection
    $stmt = $pdo->prepare("
        SELECT c.id AS collection_id, ca.name, c.quantity, ca.image_url
        FROM Collection c
        JOIN Cards ca ON c.card_id = ca.id
        WHERE c.user_id = :trade_with_user_id
    ");
    $stmt->execute([':trade_with_user_id' => $trade_with_user_id]);
    $other_user_collection = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trade</title>
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

        img {
            max-width: 100px;
            max-height: 140px;
        }
    </style>
</head>

<body>
    <h1>Trade with User</h1>
    <h2>User's Collection</h2>

    <?php if (empty($other_user_collection)): ?>
        <p>No cards found in this user's collection.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Card Name</th>
                <th>Image</th>
                <th>Quantity</th>
            </tr>
            <?php foreach ($other_user_collection as $card): ?>
                <tr>
                    <td><?= htmlspecialchars($card['name']) ?></td>
                    <td>
                        <?php if ($card['image_url']): ?>
                            <img src="<?= htmlspecialchars($card['image_url']) ?>" alt="<?= htmlspecialchars($card['name']) ?>">
                        <?php else: ?>
                            No Image Available
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($card['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <br>
    <a href="trade_search.php">Back to Search</a>
</body>

</html>