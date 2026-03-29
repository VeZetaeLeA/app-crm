<?php
namespace App\Services\CRM;

use Core\Database;

/**
 * Intelligence Service - Part of the CRM Module
 * Predictive Analytics and Machine Learning rules engine.
 */
class IntelligenceService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Predictive Analytics: Detect if a ticket is at risk of being delayed
     * based on its age, priority, and average resolution time.
     * 
     * @param array $ticket The ticket associative array
     * @return array Contains 'is_at_risk' boolean and 'risk_reason' string
     */
    public function calculateDelayRisk(array $ticket): array
    {
        // Only active/pending tickets can be at risk of delay. 
        // End or terminal states should not trigger SLA warnings.
        if (in_array($ticket['status'], ['resolved', 'closed', 'cancelled', 'void', 'budget_rejected', 'active'])) {
            return ['is_at_risk' => false, 'risk_reason' => ''];
        }

        $createdAt = new \DateTime($ticket['created_at']);
        $now = new \DateTime();
        $hoursOpen = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;

        // SLA thresholds based on priority (in hours)
        $slaLimits = [
            'critical' => 12,
            'high' => 24,
            'normal' => 48,
            'low' => 72
        ];

        $priority = $ticket['priority'] ?? 'normal';
        $maxHours = $slaLimits[$priority] ?? 48;

        // Condition 1: Ticket is dangerously close to its SLA limit (> 75% of allowed time)
        if ($hoursOpen >= ($maxHours * 0.75)) {
            return [
                'is_at_risk' => true,
                'risk_reason' => "El ticket se acerca al límite de su ANS ({$maxHours}h). Tiempo abierto: " . round($hoursOpen) . "h."
            ];
        }

        // Condition 2: No messages from staff in the last 24 hours (if ticket is older than 24h)
        if ($hoursOpen > 24 && isset($ticket['id'])) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM chat_messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.ticket_id = ? AND u.role IN ('admin', 'staff') AND m.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute([$ticket['id']]);
            $staffReplies = (int) $stmt->fetchColumn();

            if ($staffReplies === 0) {
                return [
                    'is_at_risk' => true,
                    'risk_reason' => 'Ausencia de respuesta del equipo en las últimas 24 horas.'
                ];
            }
        }

        return ['is_at_risk' => false, 'risk_reason' => ''];
    }
}
