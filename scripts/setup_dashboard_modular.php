<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/config/env.php';
EnvLoader::load(BASE_PATH . '/.env');
require_once BASE_PATH . '/vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load();

$db = Database::getInstance()->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS user_dashboard_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    widget_key VARCHAR(50) NOT NULL,
    is_visible TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    UNIQUE KEY(user_id, widget_key)
)";

try {
    $db->exec($sql);
    echo "Table user_dashboard_config created/verified.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
