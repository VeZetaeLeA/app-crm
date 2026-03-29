<?php
namespace App\Analytics;

use Core\Database;
use PDO;

/**
 * Analytics Service
 * Handles complex business intelligence calculations and data aggregation.
 */
class AnalyticsService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Calculate commercial conversion rates.
     */
    public function getCommercialConversions(): array
    {
        $stats = [
            'leads_to_tickets' => $this->getConversionRate('client', 'tickets'),
            'tickets_to_budgets' => $this->getConversionRate('tickets', 'budgets'),
            'budgets_to_invoices' => $this->getConversionRate('budgets', 'invoices'),
            'invoices_to_paid' => $this->getConversionRate('invoices', 'paid_invoices')
        ];

        return $stats;
    }

    /**
     * Get financial KPIs.
     */
    public function getFinancialKPIs(): array
    {
        $currentMonth = date('Y-m');

        $revenue = $this->db->query("SELECT SUM(total) FROM invoices WHERE status = 'paid' AND DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'")->fetchColumn();
        $totalCustomers = $this->db->query("SELECT COUNT(DISTINCT client_id) FROM active_services WHERE status = 'active'")->fetchColumn();

        return [
            'monthly_revenue' => (float) ($revenue ?: 0),
            'avg_customer_value' => $totalCustomers > 0 ? (float) ($revenue / $totalCustomers) : 0,
            'active_customers' => (int) $totalCustomers
        ];
    }

    /**
     * Generic conversion rate calculator.
     * This is a simplified version; in a real scenario, it would track the flow of IDs.
     */
    private function getConversionRate(string $from, string $to): float
    {
        $counts = [
            'client' => "SELECT COUNT(*) FROM users WHERE role = 'client'",
            'tickets' => "SELECT COUNT(*) FROM tickets WHERE status != 'void'",
            'budgets' => "SELECT COUNT(*) FROM tickets WHERE status IN ('budget_sent', 'pending_approval')",
            'invoices' => "SELECT COUNT(*) FROM invoices",
            'paid_invoices' => "SELECT COUNT(*) FROM invoices WHERE status = 'paid'"
        ];

        $fromCount = (int) $this->db->query($counts[$from])->fetchColumn();
        $toCount = (int) $this->db->query($counts[$to])->fetchColumn();

        return $fromCount > 0 ? round(($toCount / $fromCount) * 100, 2) : 0;
    }

    /**
     * Aggregate daily metrics into a summary table (to be called by AnalyticsJobs).
     */
    public function aggregateDailyMetrics(): bool
    {
        // Implementation for pre-calculating and storing metrics for performance
        // This would insert into a 'daily_analytics_summary' table
        return true;
    }
}
