<?php
/**
 * Script de mantenimiento para corregir la configuración de email en el entorno demo.
 * Sincroniza los valores de la tabla app_config con los definidos en el archivo .env actual.
 */

define('BASE_PATH', dirname(__DIR__));

// Autocarga manual simple para el script de mantenimiento
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once BASE_PATH . '/config/env.php';

try {
    echo "--- Iniciando sincronización de configuración de Email ---\n";

    // 1. Cargar Entorno
    EnvLoader::load(BASE_PATH . '/.env');
    \Core\Config::load();

    $db = \Core\Database::getInstance()->getConnection();

    // 2. Obtener valores del .env actual
    $newFromAddress = getenv('MAIL_FROM_ADDRESS');
    $newFromName = getenv('MAIL_FROM_NAME');

    if (!$newFromAddress) {
        throw new Exception("MAIL_FROM_ADDRESS no está definido en el archivo .env");
    }

    echo "Valores detectados en .env:\n";
    echo "- Remitente: $newFromAddress\n";
    echo "- Nombre: " . ($newFromName ?: '(vacío)') . "\n\n";

    // 3. Actualizar tabla app_config
    $stmt = $db->prepare("UPDATE app_config SET config_value = ? WHERE config_key = ?");

    // Actualizar Remitente
    $stmt->execute([$newFromAddress, 'mail.from_address']);
    echo "[OK] 'mail.from_address' actualizado en la base de datos.\n";

    // Actualizar Nombre
    if ($newFromName) {
        $stmt->execute([$newFromName, 'mail.from_name']);
        echo "[OK] 'mail.from_name' actualizado en la base de datos.\n";
    }

    echo "\n--- Configuración sincronizada exitosamente ---\n";
    echo "El sistema ahora usará '$newFromAddress' como remitente oficial.\n";

} catch (Exception $e) {
    echo "\n[ERROR] No se pudo completar la sincronización: " . $e->getMessage() . "\n";
    exit(1);
}
