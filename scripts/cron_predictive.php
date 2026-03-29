<?php
/**
 * Vezetaelea OS - Predictive Analytics Cron
 * Run daily via cron: php public/index.php /cron/predictive OR directly php scripts/cron_predictive.php
 */
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/env.php';
\EnvLoader::load(BASE_PATH . '/.env');
require_once BASE_PATH . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

\Core\Config::load();
$db = \Core\Database::getInstance()->getConnection();
$intelligence = new \App\Services\CRM\IntelligenceService();

echo "Starting Predictive Analytics Job...\n";

// 1. Analyze Open Tickets
$stmt = $db->query("SELECT * FROM tickets WHERE status NOT IN ('closed', 'resolved')");
$tickets = $stmt->fetchAll();

foreach ($tickets as $t) {
    $riskData = $intelligence->calculateDelayRisk($t);
    if ($riskData['is_at_risk']) {
        // Find staff to notify
        $staffStmt = $db->query("SELECT id FROM users WHERE role IN ('admin', 'staff')");
        foreach ($staffStmt->fetchAll() as $s) {
            \App\Models\Notification::send(
                $s['id'],
                'ticket_risk',
                'Riesgo de Retraso en Ticket',
                "El ticket {$t['ticket_number']} presenta riesgo: {$riskData['risk_reason']}",
                '/ticket/detail/' . $t['id']
            );
        }
        echo "Ticket {$t['ticket_number']} is at risk. Notified staff.\n";
    }
}

// 2. Analyze Active Workspaces/Projects (if applicable logic exists, placeholder for now)
echo "Predictive Analytics Job Completed.\n";
