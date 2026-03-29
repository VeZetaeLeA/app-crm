<?php
define('BASE_PATH', __DIR__);
require 'Core/Config.php';
require 'Core/Database.php';
use Core\Config;
use Core\Database;

if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES);
    foreach ($lines as $l) {
        if (strpos($l, '=') !== false)
            putenv($l);
    }
}
Config::load();

try {
    $db = Database::getInstance()->getConnection();
    echo "Updating local schema...\n";

    // 1. Phone length for encryption
    $db->exec("ALTER TABLE users MODIFY phone VARCHAR(255) NULL");
    echo "- Users phone length updated to 255.\n";

    // 2. JWT Refresh Tokens table structure
    $db->exec("CREATE TABLE IF NOT EXISTS jwt_refresh_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "- Table jwt_refresh_tokens ensured.\n";

    // 3. User Dashboard Config Unique Index
    try {
        // First try to drop old if exists, but silently
        @$db->exec("ALTER TABLE user_dashboard_config DROP INDEX user_id");
    } catch (Exception $e) {
    }

    try {
        $db->exec("ALTER TABLE user_dashboard_config ADD UNIQUE KEY user_widget_unique (user_id, widget_key)");
        echo "- Table user_dashboard_config index updated.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "- Table user_dashboard_config index already exists.\n";
        }
    }

    echo "Schema synchronization complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
