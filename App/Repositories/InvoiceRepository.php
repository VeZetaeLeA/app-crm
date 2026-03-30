<?php
namespace App\Repositories;

use PDO;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    protected string $table = 'invoices';

    public function getApprovedBudget(int $budgetId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM budgets WHERE id = ? AND status = 'approved'");
        $stmt->execute([$budgetId]);
        return $stmt->fetch() ?: null;
    }

    public function hasInvoiceForBudget(int $budgetId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM invoices WHERE budget_id = ?");
        $stmt->execute([$budgetId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getClientIdByTicket(int $ticketId): ?int
    {
        $stmt = $this->db->prepare("SELECT client_id FROM tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function createInvoice(array $data): int
    {
        $sql = "INSERT INTO invoices (invoice_number, client_id, budget_id, service_reference, issue_date, due_date, subtotal, tax_amount, total, status, created_by) 
                VALUES (?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), ?, ?, ?, 'unpaid', ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['invoice_number'],
            $data['client_id'],
            $data['budget_id'],
            $data['service_reference'],
            $data['subtotal'],
            $data['tax_amount'],
            $data['total'],
            $data['created_by']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateTicketStatus(int $ticketId, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $ticketId]);
    }

    public function getPendingPaymentReceiptsSum(int $invoiceId): float
    {
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM payment_receipts WHERE invoice_id = ? AND status = 'pending'");
        $stmt->execute([$invoiceId]);
        return (float) $stmt->fetchColumn();
    }

    public function updateInvoicePayment(int $invoiceId, string $status, float $paidAmount, bool $isFullyPaid): bool
    {
        $paid_at_sql = $isFullyPaid ? 'NOW()' : 'NULL';
        $sql = "UPDATE invoices SET status = ?, paid_amount = ?, paid_at = {$paid_at_sql} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $paidAmount, $invoiceId]);
    }

    public function verifyPendingReceipts(int $invoiceId, int $verifiedBy): bool
    {
        $sql = "UPDATE payment_receipts SET status = 'verified', verified_by = ?, verified_at = NOW() WHERE invoice_id = ? AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$verifiedBy, $invoiceId]);
    }

    public function getInvoiceWithBudgetDetails(int $invoiceId): ?array
    {
        $stmt = $this->db->prepare("SELECT i.*, b.ticket_id, b.title as budget_title FROM invoices i JOIN budgets b ON i.budget_id = b.id WHERE i.id = ?");
        $stmt->execute([$invoiceId]);
        return $stmt->fetch() ?: null;
    }

    public function getServicePlanIdByTicket(int $ticketId): ?int
    {
        $stmt = $this->db->prepare("SELECT service_plan_id FROM tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function hasActiveServiceForInvoice(int $invoiceId): bool
    {
        $stmtCheck = $this->db->prepare("SELECT id FROM active_services WHERE invoice_id = ? LIMIT 1");
        $stmtCheck->execute([$invoiceId]);
        return (bool) $stmtCheck->fetchColumn();
    }

    public function createActiveService(array $data): int
    {
        $sql = "INSERT INTO active_services (client_id, ticket_id, invoice_id, service_plan_id, name, start_date, activated_by, status) 
                VALUES (?, ?, ?, ?, ?, CURDATE(), ?, 'active')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['client_id'],
            $data['ticket_id'],
            $data['invoice_id'],
            $data['service_plan_id'],
            $data['name'],
            $data['activated_by']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findInvoiceById(int $invoiceId): ?array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$invoiceId, $tenantId]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(array $filters = []): array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $where = ["i.tenant_id = ?"];
        $params = [$tenantId];

        if (!empty($filters['client_id'])) {
            $where[] = "i.client_id = ?";
            $params[] = $filters['client_id'];
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT i.*, u.name as client_name, b.budget_number 
                FROM invoices i 
                JOIN users u ON i.client_id = u.id 
                LEFT JOIN budgets b ON i.budget_id = b.id 
                WHERE {$whereSql} 
                ORDER BY i.created_at DESC";

        return $this->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array
    {
        $tenantId = \Core\Config::get('current_tenant_id', 1);
        $sql = "SELECT i.*, u.name as client_name, u.email as client_email, u.company as client_company, u.phone as client_phone, b.budget_number 
                FROM invoices i 
                JOIN users u ON i.client_id = u.id 
                LEFT JOIN budgets b ON i.budget_id = b.id 
                WHERE i.id = ? AND i.tenant_id = ?";

        $row = $this->fetch($sql, [$id, $tenantId]);
        return $row ?: null;
    }

    public function getReceiptByInvoice(int $invoiceId): ?array
    {
        $sql = "SELECT * FROM payment_receipts WHERE invoice_id = ? ORDER BY created_at DESC LIMIT 1";
        return $this->fetch($sql, [$invoiceId]);
    }

    public function createReceipt(array $data): int
    {
        $sql = "INSERT INTO payment_receipts (invoice_id, uploaded_by, filename, filepath, amount, payment_date, status) 
                VALUES (?, ?, ?, ?, ?, CURDATE(), 'pending')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['invoice_id'],
            $data['uploaded_by'],
            $data['filename'],
            $data['filepath'],
            $data['amount']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE invoices SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
}
