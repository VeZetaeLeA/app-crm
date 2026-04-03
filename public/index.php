<?php
/**
 * Vezetaelea OS - Entry Point
 * Corrección Estructural Definitiva: Unificación de Configuración y Bootstrap
 */

// 1. Cargar el loader de entorno manualmente
require_once __DIR__ . '/../config/env.php';

// 2. Definición de ruta base global
define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/App/Views');

// 3. Autoload Estructural (Composer)
require_once BASE_PATH . '/vendor/autoload.php';

// Fallback para clases no gestionadas por composer (opcional pero seguro)
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Config;
use Core\Session;
use Core\App;
use Core\Validator;

/**
 * Helpers Globales
 */
require_once BASE_PATH . '/Core/Helpers.php';

// 4. Try/Catch Global Real para Control de Errores Catastróficos
try {
    // Carga de Variables de Entorno (.env)
    EnvLoader::load(BASE_PATH . '/.env');

    // Configuración Inicial de Errores (Antes de cargar Config)
    $env = getenv('ENVIRONMENT') ?: 'local';

    if ($env === 'production') {
        ini_set('display_errors', 0);
        error_reporting(0);
    } else {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

    // 5. Inicialización de Componentes Core (Orden Crítico)
    Config::load();
    \Core\Lang::init();
    Session::start();

    // Configuración de Timezone
    date_default_timezone_set(Config::get('timezone', 'America/Argentina/Buenos_Aires'));

    // 6. Registro de Eventos y Listeners (Evolution 2.0)
    \Core\EventDispatcher::listen(\App\Events\LeadCreated::class, [\App\Listeners\NotificationListener::class, 'handleLeadCreated']);
    \Core\EventDispatcher::listen(\App\Events\ProjectStarted::class, [\App\Listeners\NotificationListener::class, 'handleProjectStarted']);

    // 7. Arranque de la Aplicación
    Core\Security::setHeaders();
    Core\RateLimiter::check(Config::get('security.global_rate_limit', 100), 60); // req/min global
    $app = new App();

} catch (\Throwable $e) {
    http_response_code(500);

    // Logging en archivo
    error_log($e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    $env = getenv('ENVIRONMENT') ?: 'local';

    if ($env !== 'production') {
        echo '<div style="background: #fee; border: 1px solid #f00; padding: 20px; font-family: monospace; color: #000;">';
        echo '<h2 style="color: #900; margin-top: 0;">FATAL ERROR (Environment: ' . $env . ')</h2>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . $e->getFile() . ':' . $e->getLine() . '</p>';
        echo '<h3>Stack Trace:</h3>';
        echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ccc;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    } else {
        echo '<h1>Internal Server Error</h1>';
        echo '<p>Se ha producido un error técnico. Por favor, contacta con el soporte.</p>';
    }
    exit;
}
