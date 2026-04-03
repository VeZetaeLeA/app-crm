<?php
/**
 * VeZetaeLeA OS - Deploy Fix & OPcache Repair Tool
 * Access via: /public/deploy_fix.php
 * Self-destructs after execution for security.
 */
define('BASE_PATH', dirname(__DIR__));
header('Content-Type: text/plain; charset=utf-8');

echo "=== VeZetaeLeA OS — Deploy Fix & Repair Tool ===\n\n";
echo "Server: " . php_uname() . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Base Path: " . BASE_PATH . "\n\n";

// 1. Check critical files
$critical = [
    'App/Controllers/ProjectController.php',
    'App/Repositories/ProjectRepository.php',
    'App/Repositories/ProjectRepositoryInterface.php',
    'App/Repositories/BaseRepository.php',
    'vendor/autoload.php',
];

echo "--- [1] Critical File Check ---\n";
$allOk = true;
foreach ($critical as $relPath) {
    $abs = BASE_PATH . '/' . $relPath;
    if (!file_exists($abs)) {
        echo "[MISSING] $relPath\n";
        $allOk = false;
    } else {
        $fh = fopen($abs, 'r');
        $firstLine = trim(fgets($fh));
        fclose($fh);
        echo "[OK] $relPath | First line: $firstLine\n";
    }
}

// 2. OPcache clear
echo "\n--- [2] OPcache ---\n";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "[OK] OPcache cleared successfully.\n";
    } else {
        echo "[WARN] OPcache reset returned false.\n";
    }
} elseif (function_exists('opcache_invalidate')) {
    foreach ($critical as $relPath) {
        opcache_invalidate(BASE_PATH . '/' . $relPath, true);
    }
    echo "[OK] OPcache invalidated for critical files.\n";
} else {
    echo "[SKIP] OPcache functions not available on this server.\n";
}

// 3. Autoloader test
echo "\n--- [3] Autoloader & Class Resolution ---\n";
try {
    require_once BASE_PATH . '/vendor/autoload.php';
    echo "[OK] Composer autoloader loaded.\n";

    if (class_exists('\\App\\Controllers\\ProjectController')) {
        echo "[SUCCESS] ProjectController resolved correctly via autoloader.\n";
    } else {
        echo "[WARN] ProjectController not found via autoloader. Trying manual include...\n";
        $manualPath = BASE_PATH . '/App/Controllers/ProjectController.php';
        if (file_exists($manualPath)) {
            include_once $manualPath;
            echo class_exists('\\App\\Controllers\\ProjectController')
                ? "[FIXED] Class resolved after manual include.\n"
                : "[FAIL] Class still not resolvable. Check namespace or syntax error in file.\n";
        } else {
            echo "[FAIL] File does not exist: $manualPath\n";
        }
    }
} catch (\Throwable $e) {
    echo "[FATAL] " . $e->getMessage() . "\n";
}

// 4. Writable dirs
echo "\n--- [4] Directory Permissions ---\n";
$dirs = ['storage', 'logs', 'tmp', 'public/storage'];
foreach ($dirs as $d) {
    $abs = BASE_PATH . '/' . $d;
    if (!is_dir($abs)) {
        @mkdir($abs, 0755, true);
        echo "[CREATED] $d\n";
    } else {
        echo (is_writable($abs) ? "[OK]" : "[NOT WRITABLE]") . " $d\n";
    }
}

echo "\n=== Done. Script self-destructing now. ===\n";
@unlink(__FILE__);
echo "File deleted for security.\n";
