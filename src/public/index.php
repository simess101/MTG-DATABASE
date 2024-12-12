<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTG Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            margin: 0 10px;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
        }

        .main {
            margin: 20px;
        }

        .top-nav {
            display: flex;
            justify-content: flex-end;
        }

        .top-nav ul {
            display: flex;
            list-style: none;
        }

        .top-nav ul li {
            margin: 0 10px;
        }

        .top-nav ul li a {
            text-decoration: none;
            color: white;
            padding: 8px 12px;
            background-color: #007BFF;
            border-radius: 4px;
        }

        .top-nav ul li a:hover {
            background-color: #0056b3;
        }

        .button-container {
            margin: 20px 0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .button-container a {
            display: inline-block;
            padding: 15px 25px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .button-container a:hover {
            background-color: #0056b3;
        }

        .welcome {
            margin-right: 15px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Magic: The Gathering Database</h1>
        <div class="top-nav">
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="welcome">Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="search.php">Search Cards</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>
    <div class="main">
        <h2>Welcome to the Magic: The Gathering Database</h2>
        <p>Explore a vast collection of Magic: The Gathering cards, build and manage your decks, and connect with other players to trade cards.</p>

        <div class="button-container">
            <a href="user_collections.php">User Collections</a>
            <a href="user_decks.php">Decks</a>
            <a href="trade_search.php">Search for Users to Trade</a>
            <a href="search.php">Search Cards</a>
        </div>
    </div>
</body>

</html>