<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/config/env.php';
EnvLoader::load(BASE_PATH . '/.env');
require_once BASE_PATH . '/vendor/autoload.php';

use Core\Config;
use Core\Container;
use App\Controllers\DashboardController;

Config::load();

try {
    $container = new Container();
    $controller = $container->build(DashboardController::class);
    echo "SUCCESS: Container resolved DashboardController with all dependencies.\n";
    echo "Controller object: " . get_class($controller) . "\n";
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
