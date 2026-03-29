<?php
namespace Tests\Unit;

use App\Analytics\AnalyticsService;
use PHPUnit\Framework\TestCase;

class AnalyticsServiceTest extends TestCase
{
    private $pdo;
    private $analyticsService;

    protected function setUp(): void
    {
        // Use SQLite in-memory for testing if possible, 
        // but for now, we'll just mock PDO or use a test DB.
        $this->pdo = $this->createMock(\PDO::class);
        $this->analyticsService = new AnalyticsService($this->pdo);
    }

    public function testGetFinancialKPIsWithNoData()
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('fetchColumn')->willReturn(0);

        $this->pdo->method('query')->willReturn($stmt);

        $results = $this->analyticsService->getFinancialKPIs();

        $this->assertEquals(0, $results['monthly_revenue']);
        $this->assertEquals(0, $results['avg_customer_value']);
        $this->assertEquals(0, $results['active_customers']);
    }

    public function testConversionRateCalculation()
    {
        // Testing private method might require reflection or testing via public methods
        // Let's test via public getCommercialConversions
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('fetchColumn')->willReturnOnConsecutiveCalls(10, 5, 20, 10, 30, 15, 40, 20);

        $this->pdo->method('query')->willReturn($stmt);

        $conversions = $this->analyticsService->getCommercialConversions();

        $this->assertEquals(50, $conversions['leads_to_tickets']);
        $this->assertEquals(50, $conversions['tickets_to_budgets']);
    }
}
