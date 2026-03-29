<?php
namespace App\Controllers\Api;

use App\Analytics\AnalyticsService;
use Core\Config;
use Core\JWT;

/**
 * Analytics API Controller
 * Exposes BI data via JSON endpoints.
 */
class AnalyticsController extends ApiController
{
    private $analyticsService;

    public function __construct(\Core\JWT $jwt, AnalyticsService $analyticsService)
    {
        parent::__construct($jwt);
        $this->analyticsService = $analyticsService;
    }

    /**
     * GET /api/v1/analytics/conversions
     */
    public function conversions()
    {
        $this->authenticate();

        $data = $this->analyticsService->getCommercialConversions();
        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * GET /api/v1/analytics/financial
     */
    public function financial()
    {
        $user = $this->authenticate();
        if (($user['role'] ?? '') !== 'admin') {
            $this->error("Unauthorized: Admin role required", 403);
        }

        $data = $this->analyticsService->getFinancialKPIs();
        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
