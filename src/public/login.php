<?php
// Include the database connection
require_once '../includes/db_connect.php'; // Adjust path if needed

// Start session
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$username || !$password) {
        echo "Please fill in both fields!";
    } else {
        try {
            // Query to check if the user exists
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Password matches, log in the user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                // Redirect to the main page after successful login
                header("Location: index.php");
                exit;
            } else {
                echo "Invalid username or password.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <h1>Login</h1>
    <form method="POST" action="login.php">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
    <br>
    <button onclick="window.location.href='index.php';">Back to Main Page</button>
</body>

</html>