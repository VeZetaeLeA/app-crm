<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$dsn = 'mysql:host=localhost;dbname=vezetaelea;charset=utf8mb4';
$user = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('ALTER TABLE instagram_posts ADD COLUMN scenes_data JSON NULL AFTER visual_prompt;');
    echo "DB updated successfully\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
         echo "Column already exists\n";
    } else {
         echo "Connection failed: " . $e->getMessage() . "\n";
    }
}
