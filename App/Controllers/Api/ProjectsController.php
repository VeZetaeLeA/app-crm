<?php

namespace App\Controllers\Api;

use Core\Database;

/**
 * Api Projects Controller - Protected by JWT
 */
class ProjectsController extends ApiController
{
    /**
     * GET /api/v1/projects
     */
    public function index()
    {
        $user = $this->authenticate(); // JWT Protected
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT s.*, u.name as client_name, p.name as plan_name 
                FROM active_services s 
                JOIN users u ON s.client_id = u.id 
                JOIN service_plans p ON s.service_plan_id = p.id";

        if ($user['role'] === 'client') {
            $sql .= " WHERE s.client_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user['user_id']]);
        } else {
            $stmt = $db->query($sql);
        }

        $projects = $stmt->fetchAll();

        // Add progress calculation
        foreach ($projects as &$p) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM project_deliverables WHERE active_service_id = ?");
            $stmt->execute([$p['id']]);
            $current = $stmt->fetchColumn();

            $p['progress_percent'] = $p['total_deliverables'] > 0
                ? round(($current / $p['total_deliverables']) * 100)
                : 0;
            $p['current_deliverables'] = (int) $current;
        }

        $this->json([
            'success' => true,
            'count' => count($projects),
            'data' => $projects
        ]);
    }
}
