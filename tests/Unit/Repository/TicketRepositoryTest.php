<?php
declare(strict_types=1);

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Repositories\TicketRepository;
use PDO;
use PDOStatement;

/**
 * TicketRepositoryTest
 * Validates the business logic and query building for the support system.
 */
class TicketRepositoryTest extends TestCase
{
    private $pdo;
    private $stmt;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new TicketRepository($this->pdo);

        // Core Requirement: Repository must always have a tenant context.
        \Core\Config::set('current_tenant_id', 1);
    }

    /**
     * Test successful ticket retrieval by ID.
     */
    public function test_get_by_id_success()
    {
        $ticketData = [
            'id' => 42,
            'ticket_number' => 'VZ-42',
            'subject' => 'Payment issue',
            'tenant_id' => 1,
            'client_name' => 'Alice',
            'client_email' => 'alice@vz.com'
        ];

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("SELECT t.*, u.name as client_name"))
            ->willReturn($this->stmt);

        // We expect ID 42 and Tenant ID 1
        $this->stmt->expects($this->once())
            ->method('execute')
            ->with([42, 1]);

        $this->stmt->expects($this->once())
            ->method('fetch')
            ->willReturn($ticketData);

        $result = $this->repository->getById(42);

        $this->assertEquals('VZ-42', $result['ticket_number']);
        $this->assertEquals(1, $result['tenant_id']);
    }

    /**
     * Test AI Analysis update (GAI Integration).
     */
    public function test_update_ai_analysis()
    {
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("UPDATE tickets SET ai_sentiment"))
            ->willReturn($this->stmt);

        $analysis = ['urgency' => 'high', 'suggestion' => 'immediate_call'];
        
        $this->stmt->expects($this->once())
            ->method('execute')
            ->with(['positive', json_encode($analysis), 42]);

        $this->stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $success = $this->repository->updateAiAnalysis(42, 'positive', $analysis);

        $this->assertTrue($success);
    }

    /**
     * Test getting ticket stats.
     */
    public function test_get_stats()
    {
        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->expects($this->exactly(2))
            ->method('execute')
            ->with([1]); // Tenant ID

        $this->stmt->expects($this->exactly(2))
            ->method('fetchColumn')
            ->willReturnOnConsecutiveCalls(10, 3); // 10 total, 3 open

        $stats = $this->repository->getStats();

        $this->assertEquals(10, $stats['total']);
        $this->assertEquals(3, $stats['open']);
    }
}
