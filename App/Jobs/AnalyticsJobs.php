<?php
namespace App\Jobs;

use App\Analytics\AnalyticsService;
use Core\Database;

/**
 * Analytics Jobs
 * Scheduled tasks to process and aggregate business intelligence data.
 */
class AnalyticsJobs
{
    private $db;
    private $analyticsService;

    public function __construct()
    {
        $this->db = \Core\Database::getInstance()->getConnection();
        $this->analyticsService = new AnalyticsService($this->db);
    }

    /**
     * Run the daily aggregation job.
     * This should be called by worker.php or a cron script.
     */
    public function handle(): void
    {
        $logFile = __DIR__ . '/../../storage/logs/analytics.log';
        $timestamp = date('Y-m-d H:i:s');

        try {
            file_put_contents($logFile, "[$timestamp] Starting Daily Analytics Aggregation\n", FILE_APPEND);

            $success = $this->analyticsService->aggregateDailyMetrics();

            if ($success) {
                file_put_contents($logFile, "[$timestamp] Successfully aggregated metrics\n", FILE_APPEND);
            } else {
                file_put_contents($logFile, "[$timestamp] Failed to aggregate metrics\n", FILE_APPEND);
            }
        } catch (\Exception $e) {
            file_put_contents($logFile, "[$timestamp] ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
