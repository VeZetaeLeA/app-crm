<?php
namespace Core\Observability;

use Core\Database;
use Core\Config;
use PDO;

/**
 * Metrics Collector
 * Measures system and business performance metrics.
 */
class MetricsCollector
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Collect system-level metrics.
     */
    public function collectSystemMetrics(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'php_version' => PHP_VERSION,
            'server_load' => sys_getloadavg()[0] ?? 0
        ];
    }

    /**
     * Collect business-level metrics.
     */
    public function collectBusinessMetrics(): array
    {
        $metrics = [];

        // Resolution time (average)
        $metrics['avg_ticket_resolution_time'] = $this->db->query("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) 
            FROM tickets 
            WHERE status = 'closed'
        ")->fetchColumn();

        // Error rate (from login logs as a proxy for system health)
        $metrics['login_error_rate'] = $this->db->query("
            SELECT (COUNT(*) * 100 / (SELECT COUNT(*) FROM login_logs))
            FROM login_logs 
            WHERE success = 0
        ")->fetchColumn();

        return $metrics;
    }
}
