<?php
namespace App\Repositories;

use PDO;

class TicketRepository extends BaseRepository implements TicketRepositoryInterface
{
    protected string $table = 'tickets';

    public function getRecentWithClients(int $limit = 10, array $excludeStatuses = [])
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $excludeSql = " WHERE t.tenant_id = ? ";

        if (!empty($excludeStatuses)) {
            $placeholders = implode(',', array_fill(0, count($excludeStatuses), '?'));
            $excludeSql .= " AND t.status NOT IN ($placeholders) ";
        }

        $sql = "SELECT t.*, u.name as client_name, sp.name as plan_name, s.name as service_name 
                FROM {$this->table} t 
                JOIN users u ON t.client_id = u.id 
                LEFT JOIN service_plans sp ON t.service_plan_id = sp.id
                LEFT JOIN services s ON sp.service_id = s.id
                $excludeSql
                ORDER BY t.created_at DESC LIMIT ?";

        $stmt = $this->db->prepare($sql);

        $i = 1;
        $stmt->bindValue($i++, $tenantId);
        foreach ($excludeStatuses as $status) {
            $stmt->bindValue($i++, $status);
        }
        $stmt->bindValue($i, $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $total = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = 'open' AND tenant_id = ?");
        $stmt->execute([$tenantId]);
        $open = $stmt->fetchColumn();

        return [
            'total' => $total,
            'open' => $open,
        ];
    }

    public function getDistribution(): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);

        $sql = "SELECT 
                SUM(IF(status = 'open', 1, 0)) as open,
                SUM(IF(status IN ('in_analysis', 'budget_sent', 'budget_approved', 'invoiced', 'payment_pending'), 1, 0)) as in_progress,
                SUM(IF(status = 'active', 1, 0)) as resolved,
                SUM(IF(status = 'closed', 1, 0)) as closed
                FROM {$this->table} WHERE tenant_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'open' => (int) ($row['open'] ?? 0),
            'in_progress' => (int) ($row['in_progress'] ?? 0),
            'resolved' => (int) ($row['resolved'] ?? 0),
            'closed' => (int) ($row['closed'] ?? 0)
        ];
    }

    public function createTicket(array $data): int
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $sql = "INSERT INTO tickets (ticket_number, tenant_id, client_id, service_plan_id, subject, description, priority, status, sla_deadline, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['ticket_number'],
            $tenantId,
            $data['client_id'],
            $data['service_plan_id'],
            $data['subject'],
            $data['description'],
            $data['priority'] ?? 'normal',
            $data['status'],
            $data['sla_deadline'] ?? date('Y-m-d H:i:s', strtotime("+48 hours"))
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    public function assignTicket(int $id, int $staffId): bool
    {
        $sql = "UPDATE tickets SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$staffId, $id]);
    }

    public function getTicketWithClientAndPlan(int $id): ?array
    {
        $sql = "SELECT t.*, u.email, u.name as client_name 
                FROM tickets t 
                LEFT JOIN users u ON t.client_id = u.id 
                WHERE t.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        return $ticket ?: null;
    }

    // Client related queries that originally lived in TicketService
    public function getClientByEmail(string $email): ?array
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $client = $stmt->fetch();
        return $client ?: null;
    }

    public function getClientById(int $id): ?array
    {
        $sql = "SELECT email, name FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $client = $stmt->fetch();
        return $client ?: null;
    }

    public function getAll(array $filters = []): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $where = ["t.tenant_id = ?"];
        $params = [$tenantId];

        if (!empty($filters['client_id'])) {
            $where[] = "t.client_id = ?";
            $params[] = $filters['client_id'];
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT t.*, u.name as client_name, sp.name as plan_name, s.name as service_name 
                FROM {$this->table} t 
                LEFT JOIN users u ON t.client_id = u.id 
                LEFT JOIN service_plans sp ON t.service_plan_id = sp.id 
                LEFT JOIN services s ON sp.service_id = s.id 
                WHERE {$whereSql} 
                ORDER BY t.created_at DESC";

        return $this->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $sql = "SELECT t.*, u.name as client_name, u.email as client_email, u.company as client_company, sp.name as plan_name, s.name as service_name 
                FROM {$this->table} t 
                LEFT JOIN users u ON t.client_id = u.id 
                LEFT JOIN service_plans sp ON t.service_plan_id = sp.id 
                LEFT JOIN services s ON sp.service_id = s.id 
                WHERE t.id = ? AND t.tenant_id = ?";

        $row = $this->fetch($sql, [$id, $tenantId]);
        return $row ?: null;
    }

    public function getMessages(int $ticketId): array
    {
        $sql = "SELECT m.*, u.name as user_name, u.role as user_role 
                FROM chat_messages m 
                LEFT JOIN users u ON m.user_id = u.id 
                WHERE m.ticket_id = ? ORDER BY m.created_at ASC";
        return $this->fetchAll($sql, [$ticketId]);
    }

    public function getTasks(int $ticketId): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $sql = "SELECT * FROM ticket_tasks WHERE ticket_id = ? AND tenant_id = ? ORDER BY id ASC";
        return $this->fetchAll($sql, [$ticketId, $tenantId]);
    }

    // --- AI Intelligence & System Services (GAI-04, GAI-05, Tickets) ---

    public function updateAiAnalysis(int $ticketId, string $sentiment, array $analysis): bool
    {
        $sql = "UPDATE {$this->table} SET ai_sentiment = ?, ai_analysis = ? WHERE id = ?";
        return $this->db->prepare($sql)->execute([$sentiment, json_encode($analysis), $ticketId]);
    }

    public function updatePriority(int $ticketId, string $priority): bool
    {
        $sql = "UPDATE {$this->table} SET priority = ? WHERE id = ?";
        return $this->db->prepare($sql)->execute([$priority, $ticketId]);
    }

    public function createTask(int $ticketId, string $description): int
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $sql = "INSERT INTO ticket_tasks (ticket_id, tenant_id, description) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId, $tenantId, $description]);
        return (int) $this->db->lastInsertId();
    }

    public function createMessage(int $ticketId, ?int $userId, string $message, string $messageType = 'client'): int
    {
        $sql = "INSERT INTO chat_messages (ticket_id, user_id, message, message_type) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId, $userId, $message, $messageType]);
        return (int) $this->db->lastInsertId();
    }
}
