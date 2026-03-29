<?php
namespace App\Services;

use Core\Database;
use Core\Config;
use PDO;
use Exception;

/**
 * E11-013: FinOps Processor (Event Sourcing)
 * Encargado de la inmutabilidad contable.
 */
class FinOpsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra un nuevo evento contable (Inmutable)
     */
    public function recordEvent(int $invoice_id, string $type, float $amount, array $payload = [], int $userId = null)
    {
        $tenant_id = Config::get('current_tenant_id') ?: 1;
        $userId = $userId ?: (\Core\Auth::check() ? \Core\Auth::user()['id'] : 0);

        $sql = "INSERT INTO invoice_events (invoice_id, tenant_id, event_type, amount, payload, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $invoice_id,
            $tenant_id,
            $type,
            $amount,
            json_encode($payload),
            $userId
        ]);
    }

    /**
     * Calcula el balance real derivado del EventStore (Event Sourcing)
     */
    public function calculateBalance(int $invoice_id): array
    {
        $sql = "SELECT event_type, SUM(amount) as total_amount 
                FROM invoice_events 
                WHERE invoice_id = ? 
                GROUP BY event_type";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoice_id]);
        $events = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $totalValue = $events['CREATE'] ?? 0;
        $discounts = $events['DISCOUNT'] ?? 0;
        $payments = $events['APPLY_PAYMENT'] ?? 0;
        $refunds = $events['REFUND'] ?? 0;
        
        $netTotal = $totalValue - $discounts;
        $totalPaid = $payments - $refunds;
        $pending = $netTotal - $totalPaid;

        return [
            'invoice_total' => $netTotal,
            'paid_amount' => $totalPaid,
            'pending_amount' => $pending,
            'is_fully_paid' => $pending <= 0.01, // margen de error decimal
            'is_void' => isset($events['VOID'])
        ];
    }

    /**
     * Sincroniza el estado de la tabla principal 'invoices' con los eventos (Proyección)
     */
    public function syncInvoiceProjection(int $invoice_id)
    {
        $balance = $this->calculateBalance($invoice_id);
        
        $status = 'unpaid';
        if ($balance['is_void']) {
            $status = 'void';
        } elseif ($balance['is_fully_paid']) {
            $status = 'paid';
        } elseif ($balance['paid_amount'] > 0) {
            $status = 'partial';
        }

        $sql = "UPDATE invoices SET 
                paid_amount = ?, 
                status = ?, 
                updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $balance['paid_amount'],
            $status,
            $invoice_id
        ]);
    }
}
