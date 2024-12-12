<?php
// Hardcoded database credentials
$host = 'cmsc508.com'; // Database host
$dbname = '24fa_teams_24fa_team_name'; // Database name
$username = '24fa_simess'; // Database username
$password = 'Shout4_simess_JOY'; // Database password

try {
    // Establish a connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
