<?php
namespace App\Controllers\Api;

use Core\Database;
use PDO;

/**
 * Automation API Controller
 * Management of automation rules via API.
 */
class AutomationController extends ApiController
{
    private $db;

    public function __construct(\Core\JWT $jwt)
    {
        parent::__construct($jwt);
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * GET /api/v1/automation/rules
     */
    public function rules()
    {
        $user = $this->authenticate();
        if (($user['role'] ?? '') !== 'admin') {
            $this->error("Unauthorized", 403);
        }

        $stmt = $this->db->query("SELECT * FROM automation_rules ORDER BY created_at DESC");
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->json([
            'success' => true,
            'data' => $rules
        ]);
    }

    /**
     * GET /api/v1/automation/logs
     */
    public function logs()
    {
        $user = $this->authenticate();
        if (($user['role'] ?? '') !== 'admin') {
            $this->error("Unauthorized", 403);
        }

        $stmt = $this->db->query("SELECT al.*, ar.name as rule_name 
                                  FROM automation_logs al 
                                  JOIN automation_rules ar ON al.rule_id = ar.id 
                                  ORDER BY al.executed_at DESC LIMIT 50");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
