<?php
/**
 * Script de Verificación de Entorno
 * Ejecutar vía terminal: php verify_env.php
 */

require_once __DIR__ . '/config/env.php';
define('BASE_PATH', __DIR__);

spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Config;
// EnvLoader is global

try {
    \EnvLoader::load(BASE_PATH . '/.env');
    Config::load();

    echo "\n=== VeZetaeLeA Environment Verification ===\n";
    echo "Environment: " . Config::get('ENVIRONMENT') . "\n";
    echo "Base URL: " . Config::get('base_url') . "\n";
    echo "DB Host: " . Config::get('db.host') . "\n";
    echo "DB Name: " . Config::get('db.name') . "\n";
    echo "DB User: " . Config::get('db.user') . "\n";
    echo "Mail Enabled: " . (Config::get('mail_enabled') ? 'YES' : 'NO') . "\n";
    echo "Mail From: " . Config::get('mail.from_address') . "\n";
    echo "========================================\n\n";

    // Intentar conexión a DB
    echo "Testing DB connection... ";
    try {
        \Core\Database::getInstance()->getConnection();
        echo "[OK]\n";
    } catch (\Exception $e) {
        echo "[FAILED]\nError: " . $e->getMessage() . "\n";
    }

} catch (\Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
