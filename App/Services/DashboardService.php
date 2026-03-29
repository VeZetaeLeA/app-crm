<?php
namespace App\Services;

use Core\Database;
use PDO;

/**
 * Dashboard Service
 * Provides analytical data and KPIs for the dashboard.
 */
class DashboardService
{
    private $ticketRepo;
    private $userRepo;
    private $analyticsService;
    private $insightEngine;
    private $db;

    public function __construct(\App\Repositories\TicketRepository $ticketRepo, \App\Repositories\UserRepository $userRepo, \PDO $db)
    {
        $this->ticketRepo = $ticketRepo;
        $this->userRepo = $userRepo;
        $this->db = $db;
        $this->analyticsService = new \App\Analytics\AnalyticsService($db);
        $this->insightEngine = new \App\Insights\InsightEngine($db, $this->analyticsService);
    }

    /**
     * Get actionable recent tickets for admin/staff view with intelligence scoring.
     */
    public function getRecentTicketsWithClients(int $limit = 10): array
    {
        // Excluimos estados finales o que no requieren acción inmediata para el dashboard
        $exclude = ['closed', 'void', 'budget_rejected', 'resolved'];
        $tickets = $this->ticketRepo->getRecentWithClients($limit, $exclude);

        $leadService = new \App\Services\CRM\LeadService();
        $intelService = new \App\Services\CRM\IntelligenceService();

        foreach ($tickets as &$ticket) {
            $ticket['lead_score'] = $leadService->calculateScore($ticket['client_id']);

            // Inyectar riesgo ANS
            $riskData = $intelService->calculateDelayRisk($ticket);
            $ticket['is_at_risk'] = $riskData['is_at_risk'];
            $ticket['risk_reason'] = $riskData['risk_reason'];
        }

        return $tickets;
    }

    /**
     * Get the latest ticket ID for a client (for urgent support).
     */
    public function getLatestTicketId(int $clientId): ?int
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $stmt = $this->db->prepare("SELECT id FROM tickets WHERE client_id = ? AND tenant_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$clientId, $tenantId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    /**
     * Get global statistics for SuperAdmin and Admin.
     */
    public function getAdminStats(): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $ticketStats = $this->ticketRepo->getStats();

        // 1. % Tickets Cerrados: closed / (total - void)
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $totalTickets = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE status = 'void' AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $voidTickets = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE status = 'closed' AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $closedTickets = (int) $stmt->fetchColumn();

        $validTickets = $totalTickets - $voidTickets;
        $closedPct = $validTickets > 0 ? round(($closedTickets / $validTickets) * 100) : 0;

        // 2. % Monto Pagado
        $stmt = $this->db->prepare("SELECT SUM(total) as total_amount, SUM(paid_amount) as total_paid FROM invoices WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $invoiceTotals = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalAmount = (float) ($invoiceTotals['total_amount'] ?? 0);
        $totalPaid = (float) ($invoiceTotals['total_paid'] ?? 0);
        $paidPct = $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100) : 0;

        // 3. Desglose Usuarios
        $stmt = $this->db->prepare("SELECT role, COUNT(*) as count FROM users WHERE tenant_id = ? GROUP BY role");
        $stmt->execute([$tenantId]);
        $usersBreakdown = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // 4. Active Services
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM active_services WHERE status = 'active' AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $activeServices = $stmt->fetchColumn();

        // 5. Total Users
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $totalUsers = $stmt->fetchColumn();

        $stats = [
            'total_tickets' => $totalTickets,
            'open_tickets' => $ticketStats['open'],
            'closed_tickets_pct' => $closedPct,
            'active_services' => $activeServices,
            'paid_invoices_pct' => $paidPct,
            'total_users' => $totalUsers,
            'users_breakdown' => $usersBreakdown,
            'conversions' => $this->analyticsService->getCommercialConversions(),
            'financial' => $this->analyticsService->getFinancialKPIs(),
            'insights' => $this->insightEngine->getActiveInsights(),
            'sla' => $this->getSlaMetrics()
        ];

        $this->checkSlaAlerts();

        return $stats;
    }

    /**
     * Proactive SLA Monitoring (Sprint 5.3)
     */
    public function checkSlaAlerts(): void
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);

        // Find tickets breaching in less than 4 hours that haven't been notified yet
        $stmt = $this->db->prepare("SELECT id, ticket_number, subject, assigned_to 
                                   FROM tickets 
                                   WHERE sla_deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 4 HOUR) 
                                   AND status NOT IN ('closed', 'void', 'resolved') 
                                   AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $urgentTickets = $stmt->fetchAll();

        foreach ($urgentTickets as $ticket) {
            $staffId = $ticket['assigned_to'];
            
            // If not assigned, notify all active staff
            if (!$staffId) {
                $staffStmt = $this->db->prepare("SELECT id FROM users WHERE role IN ('admin', 'staff') AND tenant_id = ?");
                $staffStmt->execute([$tenantId]);
                $staff = $staffStmt->fetchAll();
            } else {
                $staff = [['id' => $staffId]];
            }

            foreach ($staff as $s) {
                \App\Models\Notification::send(
                    $s['id'], 
                    'sla_warning', 
                    '⚠️ Alerta de ANS Crítica', 
                    "El ticket {$ticket['ticket_number']} vence en menos de 4 horas. ¡Atención inmediata requerida!", 
                    '/ticket/detail/' . $ticket['id']
                );
            }
        }
    }

    /**
     * Get SLA compliance metrics (Sprint 5)
     */
    public function getSlaMetrics(): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);

        // 1. Tickets vencidos (SLA breached)
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets 
                                   WHERE sla_deadline < NOW() 
                                   AND status NOT IN ('closed', 'void', 'resolved') 
                                   AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $breached = (int) $stmt->fetchColumn();

        // 2. Tickets en riesgo (vence en < 12 horas)
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets 
                                   WHERE sla_deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 12 HOUR) 
                                   AND status NOT IN ('closed', 'void', 'resolved') 
                                   AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $atRisk = (int) $stmt->fetchColumn();

        // 3. Tickets a tiempo
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets 
                                   WHERE sla_deadline > DATE_ADD(NOW(), INTERVAL 12 HOUR) 
                                   AND status NOT IN ('closed', 'void', 'resolved') 
                                   AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $onTime = (int) $stmt->fetchColumn();

        return [
            'breached' => $breached,
            'at_risk' => $atRisk,
            'on_time' => $onTime,
            'total_active' => $breached + $atRisk + $onTime
        ];
    }

    /**
     * Get daily performance data for the last 30 days.
     */
    public function getDailyPerformance(int $days = 30): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);

        $stmtTickets = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM tickets 
                                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND tenant_id = ?
                                    GROUP BY date");
        $stmtTickets->execute([$days, $tenantId]);
        $ticketsByDate = $stmtTickets->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmtUsers = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users 
                                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND tenant_id = ?
                                 GROUP BY date");
        $stmtUsers->execute([$days, $tenantId]);
        $usersByDate = $stmtUsers->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmtClients = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users 
                                   WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND role = 'client' AND tenant_id = ?
                                   GROUP BY date");
        $stmtClients->execute([$days, $tenantId]);
        $clientsByDate = $stmtClients->fetchAll(PDO::FETCH_KEY_PAIR);

        $dailyData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dailyData[] = [
                'date' => $date,
                'tickets' => (int) ($ticketsByDate[$date] ?? 0),
                'users' => (int) ($usersByDate[$date] ?? 0),
                'clients' => (int) ($clientsByDate[$date] ?? 0)
            ];
        }

        return $dailyData;
    }

    /**
     * Get monthly performance data for the last 12 months.
     */
    public function getMonthlyPerformance(int $months = 12): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);

        $stmtTicketsM = $this->db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM tickets 
                                     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) AND tenant_id = ?
                                     GROUP BY month");
        $stmtTicketsM->execute([$months, $tenantId]);
        $ticketsByMonth = $stmtTicketsM->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmtUsersM = $this->db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM users 
                                   WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) AND tenant_id = ?
                                   GROUP BY month");
        $stmtUsersM->execute([$months, $tenantId]);
        $usersByMonth = $stmtUsersM->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmtClientsM = $this->db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM users 
                                     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) AND role = 'client' AND tenant_id = ?
                                     GROUP BY month");
        $stmtClientsM->execute([$months, $tenantId]);
        $clientsByMonth = $stmtClientsM->fetchAll(PDO::FETCH_KEY_PAIR);

        $monthlyData = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthlyData[] = [
                'month' => $month,
                'tickets' => (int) ($ticketsByMonth[$month] ?? 0),
                'users' => (int) ($usersByMonth[$month] ?? 0),
                'clients' => (int) ($clientsByMonth[$month] ?? 0)
            ];
        }

        return $monthlyData;
    }

    public function getResourceDistribution(): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $stmt = $this->db->prepare("
            SELECT sc.name as category, t.status, COUNT(*) as count 
            FROM tickets t 
            JOIN service_plans sp ON t.service_plan_id = sp.id 
            JOIN services s ON sp.service_id = s.id 
            JOIN service_categories sc ON s.category_id = sc.id 
            WHERE t.tenant_id = ?
            GROUP BY sc.name, t.status
        ");
        $stmt->execute([$tenantId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $distribution = [];
        foreach ($results as $row) {
            $cat = $row['category'];
            $status = $row['status'];
            if (!isset($distribution[$cat])) {
                $distribution[$cat] = [];
            }
            $distribution[$cat][$status] = (int) $row['count'];
        }

        return $distribution;
    }


    /**
     * Get recent leads with intelligence scoring.
     */
    public function getRecentLeadsWithScores(int $limit = 5): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.email, u.company, u.created_at,
                                  (SELECT COUNT(*) FROM tickets WHERE client_id = u.id AND tenant_id = ?) as ticket_count
                                  FROM users u WHERE u.role = 'client' AND u.tenant_id = ?
                                  ORDER BY u.created_at DESC LIMIT ?");
        $stmt->bindValue(1, $tenantId);
        $stmt->bindValue(2, $tenantId);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $leads = $stmt->fetchAll();

        $leadService = new \App\Services\CRM\LeadService();
        foreach ($leads as &$lead) {
            $lead['score'] = $leadService->calculateScore($lead['id']);
        }

        return $leads;
    }

    /**
     * Get client-specific dashboard data.
     */
    public function getClientDashboardData(int $clientId): array
    {
        // Active services
        $stmt = $this->db->prepare("SELECT ax.*, sp.name as plan_name, 
                             (SELECT COUNT(*) FROM project_deliverables pd WHERE pd.active_service_id = ax.id) as current_deliverables
                             FROM active_services ax 
                             JOIN service_plans sp ON ax.service_plan_id = sp.id 
                             WHERE ax.client_id = ? AND ax.status = 'active'");
        $stmt->execute([$clientId]);
        $services = $stmt->fetchAll();

        foreach ($services as &$s) {
            $s['progress_percent'] = ($s['total_deliverables'] > 0)
                ? round(($s['current_deliverables'] / $s['total_deliverables']) * 100)
                : 0;
            if ($s['total_deliverables'] == 0) {
                $s['progress_text'] = 'En ejecución';
            }
        }

        // Recent tickets
        $stmt = $this->db->prepare("SELECT t.*, sp.name as plan_name 
                             FROM tickets t 
                             JOIN service_plans sp ON t.service_plan_id = sp.id 
                             WHERE t.client_id = ? 
                             ORDER BY t.created_at DESC LIMIT 5");
        $stmt->execute([$clientId]);
        $tickets = $stmt->fetchAll();

        // Unpaid invoices count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM invoices WHERE client_id = ? AND status IN ('unpaid', 'pending')");
        $stmt->execute([$clientId]);
        $unpaidInvoices = $stmt->fetchColumn();

        return [
            'services' => $services,
            'tickets' => $tickets,
            'unpaid_count' => $unpaidInvoices
        ];
    }

    /**
     * Get staff-specific dashboard data.
     */
    public function getStaffDashboardData(int $staffId): array
    {
        $stmt = $this->db->prepare("SELECT t.*, u.name as client_name 
                             FROM tickets t 
                             JOIN users u ON t.client_id = u.id 
                             WHERE t.assigned_to = ? AND t.status != 'closed' 
                             ORDER BY t.priority DESC, t.created_at ASC");
        $stmt->execute([$staffId]);
        return $stmt->fetchAll();
    }

    /**
     * Get widget configuration for a specific user.
     */
    public function getWidgetConfig(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT widget_key, is_visible, sort_order FROM user_dashboard_config WHERE user_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$userId]);
        $config = $stmt->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        // Default widgets if none configured
        $defaults = [
            'stats_cards' => ['is_visible' => 1, 'sort_order' => 1],
            'performance_chart' => ['is_visible' => 1, 'sort_order' => 2],
            'resource_dist' => ['is_visible' => 1, 'sort_order' => 3],
            'bi_indicators' => ['is_visible' => 1, 'sort_order' => 4],
            'recent_tickets' => ['is_visible' => 1, 'sort_order' => 5],
            'insight_alerts' => ['is_visible' => 1, 'sort_order' => 6],
            'financial_metrics' => ['is_visible' => 1, 'sort_order' => 7],
            'sla_summary' => ['is_visible' => 1, 'sort_order' => 8]
        ];

        return array_merge($defaults, $config);
    }

    /**
     * Update widget configuration.
     */
    public function updateWidgetConfig(int $userId, array $widgets): bool
    {
        try {
            $this->db->beginTransaction();
            foreach ($widgets as $key => $config) {
                $stmt = $this->db->prepare("REPLACE INTO user_dashboard_config (user_id, widget_key, is_visible, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userId, $key, $config['is_visible'] ? 1 : 0, $config['sort_order'] ?? 0]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get financial analytics (MRR, ARR, Churn).
     */
    public function getFinancials(): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);

        // 1. MRR: Total active monthly volume
        $stmt = $this->db->prepare("
            SELECT SUM(sp.price) 
            FROM active_services asx
            JOIN service_plans sp ON asx.service_plan_id = sp.id
            WHERE asx.status = 'active' AND asx.tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $val = $stmt->fetchColumn();
        $mrr = (float)($val ?: 0);

        // 2. ARR: MRR * 12
        $arr = $mrr * 12;

        // 3. Churn Rate (Rolling 30 days)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM active_services 
            WHERE status = 'cancelled' 
            AND updated_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
            AND tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $lost = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM active_services 
            WHERE status = 'active' AND tenant_id = ?
        ");
        $stmt->execute([$tenantId]);
        $activeCount = (int) $stmt->fetchColumn();

        $startCount = $activeCount + $lost;
        $churn = ($startCount > 0) ? round(($lost / $startCount) * 100, 2) : 0;

        // 4. Monthly Revenue (Paid Invoices)
        $history = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $stmt = $this->db->prepare("
                SELECT SUM(total) 
                FROM invoices 
                WHERE status = 'paid' 
                AND DATE_FORMAT(created_at, '%Y-%m') = ? 
                AND tenant_id = ?
            ");
            $stmt->execute([$month, $tenantId]);
            $valHistory = $stmt->fetchColumn();
            $history[$month] = (float)($valHistory ?: 0);
        }

        return [
            'mrr' => $mrr,
            'arr' => $arr,
            'churn_rate' => $churn,
            'history' => $history
        ];
    }
}
