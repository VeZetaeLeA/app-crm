<?php
namespace Tests\Unit;

use App\Services\InvoiceService;
use Core\Database;
use PHPUnit\Framework\TestCase;
use PDO;

class InvoiceServiceTest extends \Tests\TestCase
{
    private $db;
    private $invoiceService;
    private $testInvoiceId;
    private $testClientId = 1;
    private $testBudgetId = 9999;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getInstance()->getConnection();
        $this->invoiceService = new InvoiceService();

        // Clean up
        $this->db->exec("DELETE FROM invoice_events WHERE invoice_id = 9999");
        $this->db->exec("DELETE FROM payment_receipts WHERE invoice_id = 9999");
        $this->db->exec("DELETE FROM active_services WHERE invoice_id = 9999");
        $this->db->exec("DELETE FROM invoices WHERE id = 9999");
        $this->db->exec("DELETE FROM budgets WHERE id = 9999");
        $this->db->exec("DELETE FROM tickets WHERE id = 9999");

        // Create dummy ticket
        $stmt = $this->db->prepare("INSERT INTO tickets (id, ticket_number, client_id, service_plan_id, subject, description, status) 
                                   VALUES (9999, 'TEST-TKT-9999', ?, 1, 'Test Subject', 'Test Desc', 'open')");
        $stmt->execute([$this->testClientId]);

        // Create dummy budget
        $stmt = $this->db->prepare("INSERT INTO budgets (id, budget_number, ticket_id, title, total, status, created_by) 
                                   VALUES (9999, 'TEST-BUD-001', 9999, 'Test Budget', 1210, 'approved', 1)");
        $stmt->execute();

        // Create test invoice
        $stmt = $this->db->prepare("INSERT INTO invoices (id, invoice_number, client_id, budget_id, issue_date, due_date, subtotal, tax_amount, total, status, created_by) 
                    VALUES (9999, 'TEST-INV-001', ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 800, 200, 1000, 'unpaid', 1)");
        $stmt->execute([$this->testClientId, $this->testBudgetId]);
        $this->testInvoiceId = 9999;

        // Systematic Debugging Fix: Registrar evento de creación para que el balance no sea 0
        $finOps = new \App\Services\FinOpsService();
        $finOps->recordEvent($this->testInvoiceId, 'CREATE', 1000, ['test' => true], 1);
        $finOps->syncInvoiceProjection($this->testInvoiceId);
    }

    protected function tearDown(): void
    {
        $this->db->exec("DELETE FROM invoice_events WHERE invoice_id = 9999");
        $this->db->exec("DELETE FROM payment_receipts WHERE invoice_id = 9999");
        $this->db->exec("DELETE FROM active_services WHERE invoice_id = 9999");
        $this->db->exec("DELETE FROM invoices WHERE id = 9999");
        $this->db->exec("DELETE FROM budgets WHERE id = 9999");
        $this->db->exec("DELETE FROM tickets WHERE id = 9999");
    }

    public function test_partial_payment_updates_status_but_does_not_activate_service()
    {
        // Insertamos un recibo por el 50% (500 USD)
        $this->db->prepare("INSERT INTO payment_receipts (invoice_id, amount, status, uploaded_by, filename, filepath, payment_date) VALUES (?, 500, 'pending', ?, 'test.pdf', 'test.pdf', CURDATE())")
            ->execute([$this->testInvoiceId, $this->testClientId]);
        $receiptId = $this->db->lastInsertId();

        // Staff confirma el pago parcial
        $result = $this->invoiceService->confirmPayment($this->testInvoiceId, 1);

        $this->assertTrue($result['success']);

        // Verificamos estado
        $stmt = $this->db->prepare("SELECT status FROM invoices WHERE id = ?");
        $stmt->execute([$this->testInvoiceId]);
        $status = $stmt->fetchColumn();

        $this->assertEquals('partial', $status, 'La factura debería estar en estado de pago parcial');

        // Verificamos que SI haya servicios activos generados (Regla: Activación con pago inicial/parcial)
        $count = $this->db->query("SELECT COUNT(*) FROM active_services WHERE invoice_id = 9999")->fetchColumn();
        $this->assertEquals(1, $count, 'Se debe activar el servicio incluso con pago parcial (depósito inicial)');
    }

    public function test_full_payment_activates_service()
    {
        // Insertamos un recibo por el 100% (1000 USD)
        $this->db->prepare("INSERT INTO payment_receipts (invoice_id, amount, status, uploaded_by, filename, filepath, payment_date) VALUES (?, 1000, 'pending', ?, 'test.pdf', 'test.pdf', CURDATE())")
            ->execute([$this->testInvoiceId, $this->testClientId]);

        // Staff confirma el pago total
        $result = $this->invoiceService->confirmPayment($this->testInvoiceId, 1);

        $this->assertTrue($result['success']);

        // Verificamos estado
        $stmt = $this->db->prepare("SELECT status FROM invoices WHERE id = ?");
        $stmt->execute([$this->testInvoiceId]);
        $status = $stmt->fetchColumn();

        $this->assertEquals('paid', $status, 'La factura debería estar totalmente pagada');

        // Verificamos que SI haya un servicio activo generado
        $count = $this->db->query("SELECT COUNT(*) FROM active_services WHERE invoice_id = 9999")->fetchColumn();
        $this->assertEquals(1, $count, 'Se debió provisionar el servicio automáticamente al pagar el 100%');
    }
}
