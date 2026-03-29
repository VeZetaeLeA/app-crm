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

    echo "Running migration for 2FA columns...\n";
    $db->exec("ALTER TABLE users ADD COLUMN two_factor_secret VARCHAR(32) DEFAULT NULL, ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0;");
    echo "Migration completed successfully!\n";

} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
