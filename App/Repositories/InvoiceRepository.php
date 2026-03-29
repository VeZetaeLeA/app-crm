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
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$invoiceId]);
        return $stmt->fetch() ?: null;
    }
}
