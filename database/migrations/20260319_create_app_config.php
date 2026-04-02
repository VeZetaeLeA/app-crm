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

    echo "Creating app_config table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS app_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT DEFAULT 1,
        config_key VARCHAR(100) NOT NULL,
        config_value TEXT,
        config_group VARCHAR(50) DEFAULT 'general',
        field_type ENUM('text', 'textarea', 'number', 'bool', 'select') DEFAULT 'text',
        label VARCHAR(255),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY(tenant_id, config_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);

    // Initial data
    echo "Inserting default configurations...\n";
    $defaults = [
        ['business.company_name', getenv('APP_NAME') ?: 'Vezetaelea CRM', 'branding', 'text', 'Nombre de la Empresa'],
        ['business.support_email', getenv('MAIL_FROM_ADDRESS') ?: 'soporte@vezetaelea.com', 'branding', 'text', 'Email de Soporte'],
        ['mail.from_name', getenv('MAIL_FROM_NAME') ?: 'Vezetaelea CRM', 'mail', 'text', 'Nombre Remitente Email'],
        ['mail.from_address', getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@vezetaelea.com', 'mail', 'text', 'Dirección Remitente Email'],
        ['limits.max_upload_size', '10485760', 'system', 'number', 'Tamaño Máximo Upload (bytes)'],
        ['security.force_2fa', '1', 'security', 'bool', 'Forzar 2FA para Staff'],
        ['ui.theme_color', getenv('UI_PRIMARY_COLOR') ?: '#D4AF37', 'ui', 'text', 'Color de Marca (HEX)'],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO app_config (config_key, config_value, config_group, field_type, label) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaults as $row) {
        $stmt->execute($row);
    }

    echo "Migration completed successfully!\n";

} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
