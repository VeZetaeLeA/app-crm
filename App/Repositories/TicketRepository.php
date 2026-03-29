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
        $sql = "INSERT INTO tickets (ticket_number, client_id, service_plan_id, subject, description, priority, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['ticket_number'],
            $data['client_id'],
            $data['service_plan_id'],
            $data['subject'],
            $data['description'],
            $data['priority'] ?? 'normal',
            $data['status']
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

    public function createClient(array $data): int
    {
        $sql = "INSERT INTO users (uuid, name, email, phone, company, password, role, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'client', 1, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['uuid'],
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['company'] ?? null,
            $data['password']
        ]);

        return (int) $this->db->lastInsertId();
    }
}
