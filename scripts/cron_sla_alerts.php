<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/env.php';

try {
    EnvLoader::load(BASE_PATH . '/.env');
} catch (Exception $e) {
    die("Error loading .env: " . $e->getMessage());
}

require_once BASE_PATH . '/vendor/autoload.php';

use Core\Database;
use Core\Mail;
use App\Models\Notification;

/**
 * SLA Alert Worker
 * Runs every hour to check tickets approaching deadline (< 12h)
 */

$db = Database::getInstance()->getConnection();

// Select open/priority tickets with SLA expiring in less than 12 hours that haven't been notified yet
// We can use a metadata column or just check the time
$sql = "SELECT t.*, u.name as client_name, u.email as client_email 
        FROM tickets t
        JOIN users u ON t.client_id = u.id
        WHERE t.status NOT IN ('closed', 'void', 'resolved')
        AND t.sla_deadline IS NOT NULL
        AND t.sla_deadline <= DATE_ADD(NOW(), INTERVAL 12 HOUR)
        AND t.sla_deadline > NOW()";

$tickets = $db->query($sql)->fetchAll();

foreach ($tickets as $t) {
    // Audit check: have we already logged an 'sla_risk_alert' for this ticket in the last 12h?
    $checkSql = "SELECT id FROM audit_logs WHERE event = 'sla_risk_alert' AND target_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)";
    $stmt = $db->prepare($checkSql);
    $stmt->execute([$t['id']]);
    if ($stmt->fetch()) continue;

    // Send Alert to Staff/Admins
    $staffStmt = $db->query("SELECT id, email FROM users WHERE role IN ('admin', 'staff')");
    $staff = $staffStmt->fetchAll();
    
    foreach ($staff as $s) {
        Notification::send($s['id'], 'sla_risk', '⚠️ Ticket en Riesgo (SLA)', 
            "El ticket #{$t['ticket_number']} vence en menos de 12 horas.", 
            '/ticket/detail/' . $t['id']);
    }

    // Log the alert
    \Core\SecurityLogger::log('sla_risk_alert', [
        'ticket_id' => $t['id'],
        'ticket_number' => $t['ticket_number'],
        'deadline' => $t['sla_deadline']
    ]);
    
    echo "Alert sent for ticket #{$t['ticket_number']}\n";
}

// Check for already expired tickets
$expiredSql = "SELECT t.* FROM tickets t
               WHERE t.status NOT IN ('closed', 'void', 'resolved')
               AND t.sla_deadline < NOW()";
$expired = $db->query($expiredSql)->fetchAll();

foreach ($expired as $t) {
    $checkSql = "SELECT id FROM audit_logs WHERE event = 'sla_expired_alert' AND target_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $stmt = $db->prepare($checkSql);
    $stmt->execute([$t['id']]);
    if ($stmt->fetch()) continue;

    $staffStmt = $db->query("SELECT id FROM users WHERE role IN ('admin', 'staff')");
    foreach ($staffStmt->fetchAll() as $s) {
        Notification::send($s['id'], 'sla_expired', '🚨 SLA VENCIDO', 
            "El ticket #{$t['ticket_number']} ha EXCEDIDO su tiempo de respuesta.", 
            '/ticket/detail/' . $t['id']);
    }

    \Core\SecurityLogger::log('sla_expired_alert', [
        'ticket_id' => $t['id'],
        'ticket_number' => $t['ticket_number']
    ]);
    
    echo "Expired alert sent for ticket #{$t['ticket_number']}\n";
}
