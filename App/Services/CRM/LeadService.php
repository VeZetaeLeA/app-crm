<?php
namespace App\Services\CRM;

use Core\Database;
use Core\Config;

/**
 * Lead Service - Part of the CRM Module
 * Manages lead scoring and intelligence.
 */
class LeadService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Calculate scoring for a lead (Ticket client)
     */
    public function calculateScore(int $clientId): int
    {
        if (!Config::get('intelligence.lead_scoring', false)) {
            return 0;
        }

        $tenantId = Config::get('current_tenant_id', 1);
        $score = 0;

        // 1. Check if it's a company email (domain check)
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$clientId]);
        $email = $stmt->fetchColumn();

        if ($email && !$this->isPublicEmail($email)) {
            $score += 20;
        }

        // 2. Check number of tickets
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE client_id = ?");
        $stmt->execute([$clientId]);
        $ticketCount = (int) $stmt->fetchColumn();
        $score += min($ticketCount * 5, 25);

        // 3. Check approved budgets
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM budgets WHERE ticket_id IN (SELECT id FROM tickets WHERE client_id = ?) AND status = 'approved'");
        $stmt->execute([$clientId]);
        $approvedCount = (int) $stmt->fetchColumn();
        $score += $approvedCount * 15;

        // 4. Activity in the last 7 days (Tickets created)
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE client_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stmt->execute([$clientId]);
        if ($stmt->fetchColumn() > 0) {
            $score += 10;
        }

        // 5. User Engagement: Chat Messages
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM chat_messages WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$clientId]);
        $messageCount = (int) $stmt->fetchColumn();
        $score += min($messageCount * 2, 20); // Up to 20 points for active communication

        // 6. Platform Usage: Recent Logins
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM audit_logs WHERE user_id = ? AND action = 'login_success' AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)");
        $stmt->execute([$clientId]);
        $loginCount = (int) $stmt->fetchColumn();
        $score += min($loginCount * 3, 15); // Up to 15 points for frequent platform access

        return min($score, 100); // Cap at 100
    }

    private function isPublicEmail(string $email): bool
    {
        $publicDomains = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'icloud.com'];
        $domain = substr(strrchr($email, "@"), 1);
        return in_array(strtolower($domain), $publicDomains);
    }
}
