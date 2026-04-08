<?php
namespace App\Insights;

use Core\Database;
use App\Analytics\AnalyticsService;
use PDO;

/**
 * Insight Engine
 * Generates proactive recommendations and alerts for the workspace.
 */
class InsightEngine
{
    private $db;
    private $analytics;

    public function __construct(PDO $db, AnalyticsService $analytics)
    {
        $this->db = $db;
        $this->analytics = $analytics;
    }

    /**
     * Generate actionable insights for the dashboard.
     */
    public function getActiveInsights(): array
    {
        $insights = [];

        // 1. Check for overdue invoices
        $overdueInvoices = $this->db->query("
            SELECT i.id, i.total 
            FROM invoices i
            JOIN budgets b ON i.budget_id = b.id
            JOIN tickets t ON b.ticket_id = t.id
            WHERE i.status = 'unpaid' 
            AND i.due_date < CURDATE()
            AND t.status != 'void'
        ")->fetchAll();
        if (count($overdueInvoices) > 0) {
            $insights[] = [
                'type' => 'alert',
                'level' => 'high',
                'message' => 'Se detectaron ' . count($overdueInvoices) . ' facturas vencidas.',
                'action_label' => 'Gestionar Cobros',
                'action_url' => '/admin/invoices?status=unpaid'
            ];
        }

        // 2. Check for idle tickets
        $idleTickets = $this->db->query("SELECT id FROM tickets WHERE status NOT IN ('closed', 'void') AND updated_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)")->fetchAll();
        if (count($idleTickets) > 0) {
            $insights[] = [
                'type' => 'recommendation',
                'level' => 'medium',
                'message' => 'Hay ' . count($idleTickets) . ' tickets sin actividad por más de 48h.',
                'action_label' => 'Revisar Tickets',
                'action_url' => '/ticket?filter=idle'
            ];
        }

        // 3. High conversion probability leads
        $highScoreLeads = $this->db->query("SELECT u.id, u.name, 
            (SELECT t.id FROM tickets t WHERE t.client_id = u.id ORDER BY t.created_at DESC LIMIT 1) as last_ticket_id
            FROM users u WHERE u.role = 'client' 
            AND u.id IN (SELECT client_id FROM tickets WHERE status != 'void') LIMIT 3")->fetchAll();

        foreach ($highScoreLeads as $lead) {
            $actionUrl = $lead['last_ticket_id'] ? '/ticket/detail/' . $lead['last_ticket_id'] : '/ticket';
            $insights[] = [
                'type' => 'opportunity',
                'level' => 'low',
                'message' => 'Cliente ' . $lead['name'] . ' tiene alta probabilidad de conversión.',
                'action_label' => 'Contactar',
                'action_url' => $actionUrl
            ];
        }

        return $insights;
    }
}
