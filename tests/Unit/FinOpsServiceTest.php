<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FinOpsService;
use Core\Database;

class FinOpsServiceTest extends TestCase
{
    private $finOps;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getInstance()->getConnection();
        $this->finOps = new FinOpsService();
        
        // Clean up test data
        $this->db->exec("DELETE FROM invoice_events WHERE invoice_id = 9999");
    }

    public function test_record_event_and_calculate_balance()
    {
        // 1. Record CREATE event
        $this->finOps->recordEvent(9999, 'CREATE', 1000.00, ['test' => true], 1);
        
        $balance = $this->finOps->calculateBalance(9999);
        $this->assertEquals(1000.00, $balance['invoice_total']);
        $this->assertEquals(1000.00, $balance['pending_amount']);
        $this->assertFalse($balance['is_fully_paid']);

        // 2. Record PAYMENT event
        $this->finOps->recordEvent(9999, 'APPLY_PAYMENT', 400.00, [], 1);
        
        $balance = $this->finOps->calculateBalance(9999);
        $this->assertEquals(400.00, $balance['paid_amount']);
        $this->assertEquals(600.00, $balance['pending_amount']);
        $this->assertFalse($balance['is_fully_paid']);

        // 3. Record full PAYMENT
        $this->finOps->recordEvent(9999, 'APPLY_PAYMENT', 600.00, [], 1);
        
        $balance = $this->finOps->calculateBalance(9999);
        $this->assertEquals(1000.00, $balance['paid_amount']);
        $this->assertEquals(0.00, $balance['pending_amount']);
        $this->assertTrue($balance['is_fully_paid']);
    }

    public function test_void_invoice()
    {
        $this->finOps->recordEvent(9999, 'CREATE', 500.00, [], 1);
        $this->finOps->recordEvent(9999, 'VOID', 0, ['reason' => 'cancelled'], 1);
        
        $balance = $this->finOps->calculateBalance(9999);
        $this->assertTrue($balance['is_void']);
    }

    protected function tearDown(): void
    {
        $this->db->exec("DELETE FROM invoice_events WHERE invoice_id = 9999");
        parent::tearDown();
    }
}
