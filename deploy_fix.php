<?php
/**
 * RECUERDA: DESPLEGAR ESTE ARCHIVO A GITHUB PARA PODER EJECUTARLO EN LA DEMO
 * URL: https://vezetaelea.com/demo/VeZetaeLeA/app-crm/public/deploy_fix.php 
 * https://vezetaelea.com/demo/VeZetaeLeA/app-crm/public/repair_vzl.php (o tu ruta actual)
 */

define('BASE_PATH', __DIR__);

header('Content-Type: text/plain');

echo "=== VeZetaeLeA OS: Deployment Fix & Autoloader Repair ===\n\n";

// 1. Verificar existencia de archivos críticos
$filesToCheck = [
    'App/Controllers/ProjectController.php',
    'App/Repositories/ProjectRepository.php',
    'App/Repositories/UserRepositoryInterface.php',
    'Core/App.php'
];

echo "1. Checking critical files:\n";
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        $firstLine = fgets(fopen($file, 'r'));
        echo "[OK] $file ($size bytes) - Start: " . trim($firstLine) . "\n";
    } else {
        echo "[ERROR] Missing: $file\n";
    }
}

// 2. Intentar limpiar cache de OPcache (si existe)
echo "\n2. Clearing OPcache:\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "[OK] OPcache reset success.\n";
} else {
    echo "[SKIP] OPcache not available.\n";
}

// 3. Verificar permisos de la carpeta storage y logs
echo "\n3. Checking Directory Permissions:\n";
$dirsToPerm = ['storage', 'logs', 'tmp', 'public/storage'];
foreach ($dirsToPerm as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "[OK] $dir ($perms) - Is Writable: " . (is_writable($dir) ? 'YES' : 'NO') . "\n";
    } else {
        mkdir($dir, 0755, true);
        echo "[CREATED] $dir\n";
    }
}

// 4. Forzar autocarga manual por si el autoloader de composer falló en la demo
echo "\n4. Classes mapping verification:\n";
try {
    require_once 'vendor/autoload.php';
    echo "[OK] Composer Autoloader loaded.\n";
    
    if (class_exists('\\App\\Controllers\\ProjectController')) {
        echo "[SUCCESS] ProjectController is now visible to PHP.\n";
    } else {
        echo "[ALERT] ProjectController is still not visible via Autoloader.\n";
        echo "Attempting manual inclusion...\n";
        include_once 'App/Controllers/ProjectController.php';
        if (class_exists('\\App\\Controllers\\ProjectController')) {
            echo "[FIXED] Manual include successful.\n";
        }
    }
} catch (\Exception $e) {
    echo "[FATAL] Autoload error: " . $e->getMessage() . "\n";
}

echo "\n=== Proceso finalizado. Si ves [SUCCESS] o [FIXED], ya puedes usar /project/workspace ===\n";
unlink(__FILE__); // Autodestrucción por seguridad
echo "Script eliminado por seguridad.\n";
