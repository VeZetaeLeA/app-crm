#!/usr/bin/env php
<?php
/**
 * Script de Validación PRD - DataWyrd
 * Verifica que todos los requisitos del PRD estén implementados
 * 
 * Uso: php validate_prd.php
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  VALIDADOR PRD TÉCNICO - DATA WYRD OS                    ║\n";
echo "║  Versión 1.3.0 - Certificación BOOTSTRAP                ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

$errors = [];
$warnings = [];
$passed = 0;
$total = 0;

function test($description, $condition, $critical = true)
{
    global $errors, $warnings, $passed, $total;
    $total++;

    if ($condition) {
        echo "✅ $description\n";
        $passed++;
    } else {
        if ($critical) {
            echo "❌ $description\n";
            $errors[] = $description;
        } else {
            echo "⚠️  $description\n";
            $warnings[] = $description;
        }
    }
}

echo "📋 VALIDANDO REQUISITOS PRD...\n\n";

// 1. Gestión de Entornos y Estructura
echo "1️⃣  ESTRUCTURA Y ENTORNOS\n";
test("Core/Config.php existe (Case-sensitive)", file_exists(__DIR__ . '/Core/Config.php'));
test("App/Controllers existe (Case-sensitive)", is_dir(__DIR__ . '/App/Controllers'));
test("Archivo .env.example existe", file_exists(__DIR__ . '/.env.example'));

$configContent = file_get_contents(__DIR__ . '/Core/Config.php');
test(
    "Validación de ENVIRONMENT implementada",
    strpos($configContent, "validEnvironments") !== false &&
    strpos($configContent, "FATAL ERROR") !== false
);

$envExample = file_get_contents(__DIR__ . '/.env.example');
test("Variable ENVIRONMENT en .env.example", strpos($envExample, 'ENVIRONMENT=') !== false);
test("Variable APP_URL en .env.example", strpos($envExample, 'APP_URL=') !== false);

// 2. Archivos de Configuración
echo "\n2️⃣  CONFIGURACIÓN POR ENTORNO\n";
test("config/app.php existe", file_exists(__DIR__ . '/config/app.php'));
test("config/local.php existe", file_exists(__DIR__ . '/config/local.php'));
test("config/demo.php existe", file_exists(__DIR__ . '/config/demo.php'));
test("config/production.php existe", file_exists(__DIR__ . '/config/production.php'));

$demoConfig = file_get_contents(__DIR__ . '/config/demo.php');
test("DEMO: debug = false", strpos($demoConfig, "'debug' => false") !== false);
test("DEMO: display_errors = false", strpos($demoConfig, "'display_errors' => false") !== false);

// 3. Manejo de Errores y Autoload
echo "\n3️⃣  BOOTSTRAP Y AUTOLOAD\n";
$indexContent = file_get_contents(__DIR__ . '/public/index.php');
test("BASE_PATH definido", strpos($indexContent, "define('BASE_PATH'") !== false);
test("Autoload estructural implementado", strpos($indexContent, "spl_autoload_register") !== false);
test("Manejo global de errores (try/catch Throwable)", strpos($indexContent, 'catch (\Throwable $e)') !== false);
test("Registro de errores persistente", strpos($indexContent, "ini_set('error_log'") !== false);
test("Helper config() eliminado (unificación en Clase Config)", strpos($indexContent, "function config(") === false);
test("Directorio storage/logs existe", is_dir(__DIR__ . '/storage/logs'));

// 4. Sistema de Correo
echo "\n4️⃣  SISTEMA DE CORREO\n";
$mailContent = file_get_contents(__DIR__ . '/Core/Mail.php');
test("Mail.php verifica mail_enabled a través de Config", strpos($mailContent, "Config::get('mail_enabled'") !== false);

// 5. Seguridad de Sesiones
echo "\n5️⃣  SEGURIDAD DE SESIONES\n";
$sessionContent = file_get_contents(__DIR__ . '/Core/Session.php');
test("Session configura httponly", strpos($sessionContent, "'httponly' => true") !== false);
test("Session configura samesite", strpos($sessionContent, "'samesite' => 'Lax'") !== false);

$authContent = file_get_contents(__DIR__ . '/App/Controllers/AuthController.php');
test("Login regenera ID de sesión", strpos($authContent, 'session_regenerate_id(true)') !== false);

// 6. Protección CSRF
echo "\n6️⃣  PROTECCIÓN CSRF\n";
$appContent = file_get_contents(__DIR__ . '/Core/App.php');
test("CSRF validado en Core/App.php", strpos($appContent, 'verifyCsrfToken') !== false);

// 7. Subida de Archivos
echo "\n7️⃣  SUBIDA DE ARCHIVOS\n";
$validatorContent = file_get_contents(__DIR__ . '/Core/Validator.php');
test("Validator valida MIME real", strpos($validatorContent, 'FILEINFO_MIME_TYPE') !== false);
test("public/storage/.htaccess existe", file_exists(__DIR__ . '/public/storage/.htaccess'));

// 8. Autorización
echo "\n8️⃣  AUTORIZACIÓN\n";
test("Directorio App/Policies existe", is_dir(__DIR__ . '/App/Policies'));

// Resumen
echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  RESULTADO DE VALIDACIÓN                                 ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

$percentage = round(($passed / $total) * 100, 1);
echo "✅ Pruebas pasadas: $passed/$total ($percentage%)\n";

if (count($errors) > 0) {
    echo "\n❌ ERRORES CRÍTICOS:\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
}

echo "\n";

if (count($errors) === 0) {
    echo "🎉 ¡CERTIFICACIÓN APROBADA! (BOOTSTRAP FIX COMPLETED)\n";
    echo "   El sistema cumple con todos los requisitos del PRD de estabilidad.\n";
    echo "   ✅ Listo para deploy a DEMO\n";
    exit(0);
} else {
    echo "🚫 CERTIFICACIÓN RECHAZADA\n";
    echo "   Corrige los errores críticos antes de continuar.\n";
    echo "   ❌ NO apto para deploy a DEMO\n";
    exit(1);
}
