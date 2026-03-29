<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/config/env.php';
EnvLoader::load(BASE_PATH . '/.env');
require_once BASE_PATH . '/vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load();

$db = Database::getInstance()->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS jwt_refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id)
)";

try {
    $db->exec($sql);
    echo "Table jwt_refresh_tokens created/verified.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
