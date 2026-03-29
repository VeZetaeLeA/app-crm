<?php
require_once __DIR__ . '/../../config/env.php';
define('BASE_PATH', dirname(dirname(__DIR__)));
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Database;
use Core\Config;

try {
    EnvLoader::load(BASE_PATH . '/.env');
    Config::load();
    $db = Database::getInstance()->getConnection();

    echo "Creating sessions table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `sessions` (
        `id` VARCHAR(128) NOT NULL,
        `payload` TEXT NOT NULL,
        `last_activity` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED DEFAULT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `user_agent` TEXT DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_sessions_last_activity` (`last_activity`),
        KEY `idx_sessions_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'sessions' created successfully!\n";

} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
