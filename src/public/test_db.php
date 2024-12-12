<?php
try {
  $pdo = new PDO("mysql:host=cmsc508.com;dbname=24fa_teams_24fa_team_name", "24fa_simess", "Shout4_simess_JOY");
  echo "Database connection successful!";
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
