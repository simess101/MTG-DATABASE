<?php
// Include the database connection
require_once '../includes/db_connect.php'; // Adjust path if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect form data
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    // Check if all fields are provided
    if (!$username || !$email || !$password) {
        echo "All fields are required!";
        exit;
    }

    try {
        // Use the correct column name: password_hash
        $stmt = $pdo->prepare("
            INSERT INTO Users (username, email, password_hash) 
            VALUES (:username, :email, :password_hash)
        ");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT) // Hash the password securely
        ]);

        // Redirect to login page after successful registration
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <h1>Register</h1>
    <form method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>

        <button type="submit">Register</button>
    </form>
    <br>
    <button onclick="window.location.href='index.php';">Back to Main Page</button>
</body>

</html>