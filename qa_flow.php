<?php
// Minimal Bootstrap
define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/vendor/autoload.php';

// Helpers
function url($path = '')
{
    return 'http://localhost/' . $path;
}
function translateStatus($status)
{
    return $status;
}
function base_path($path = '')
{
    return BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
}
function csrf_field()
{
    return '';
}

use Core\Config;
use Core\Session;
use Core\Database;

try {
    EnvLoader::load(BASE_PATH . '/.env');
    Config::load();
    Session::start();

    echo "--- Iniciando QA de Flujo de Solicitud ---\n";

    $db = Database::getInstance()->getConnection();

    // Simular POST
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $testEmail = 'qa' . time() . '@example.com';
    $_POST = [
        'name' => 'QA Tester',
        'email' => $testEmail,
        'subject' => 'Prueba de Flujo UX Fase 1',
        'description' => 'Descripción de prueba.',
        'service_id' => 1,
        'service_plan_id' => 1
    ];

    echo "1. Ejecutando TicketController::submit()...\n";
    $controller = new \App\Controllers\TicketController();

    // Capturamos para evitar que el exit() rompa todo si ocurre
    // Nota: TicketController tiene redirects con exit.
    try {
        ob_start();
        $controller->submit();
        ob_get_clean();
    } catch (\Throwable $t) {
        // En caso de que haya algún error en el proceso
        echo "Excepción capturada (posiblemente por redirect): " . $t->getMessage() . "\n";
    }

    echo "2. Verificando base de datos...\n";
    $stmt = $db->prepare("SELECT * FROM tickets WHERE subject = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['Prueba de Flujo UX Fase 1']);
    $ticket = $stmt->fetch();

    if ($ticket) {
        echo "✅ Ticket creado: " . $ticket['ticket_number'] . " (ID: " . $ticket['id'] . ")\n";

        // Verificar si se creó el usuario
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$testEmail]);
        $user = $stmt->fetch();
        if ($user) {
            echo "✅ Usuario creado: " . $user['name'] . " (" . $user['role'] . ")\n";
        }
    } else {
        echo "❌ Ticket NO creado.\n";
    }

    echo "3. Simulando envío de Email Premium (Check logs manualmente)...\n";
    // El método Mail::sendRequestConfirmation ya fue llamado en submit()

    echo "--- QA Finalizado ---\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
