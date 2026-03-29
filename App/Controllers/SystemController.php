<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Core\Config;

class SystemController extends Controller
{
    /**
     * Health Check Endpoint for Observability (PRD 9.5)
     * Verifies critical system components.
     */
    public function health()
    {
        header('Content-Type: application/json');

        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'requestId' => \Core\App::$requestId,
            'checks' => []
        ];

        // 1. Database Check
        try {
            $db = Database::getInstance()->getConnection();
            $db->query("SELECT 1");
            $health['checks']['database'] = 'OK';
        } catch (\Exception $e) {
            $health['checks']['database'] = 'FAIL: ' . $e->getMessage();
            $health['status'] = 'degraded';
        }

        // 2. Storage Check
        $storagePath = BASE_PATH . '/storage';
        if (is_writable($storagePath)) {
            $health['checks']['storage'] = 'OK';
        } else {
            $health['checks']['storage'] = 'FAIL: Not writable';
            $health['status'] = 'error';
        }

        // 3. Queue Check (Jobs table)
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as count FROM jobs WHERE status = 'failed'");
            $failedJobs = $stmt->fetch()['count'];
            $health['checks']['queues'] = "OK ({$failedJobs} failed)";
        } catch (\Exception $e) {
            $health['checks']['queues'] = 'FAIL: ' . $e->getMessage();
        }

        // 4. Memory/Disk
        $health['checks']['memory_usage'] = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
        $health['checks']['disk_free'] = round(disk_free_space(BASE_PATH) / 1024 / 1024 / 1024, 2) . ' GB';

        if ($health['status'] !== 'healthy') {
            http_response_code(503);
        }

        echo json_encode($health);
    }
}
