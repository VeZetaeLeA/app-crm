<?php
declare(strict_types=1);

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Repositories\ProjectRepository;
use PDO;
use PDOStatement;

/**
 * ProjectRepositoryTest
 * Tests the repository layer decoupling and query logic.
 */
class ProjectRepositoryTest extends TestCase
{
    private $pdo;
    private $stmt;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        // We utilize dynamic mocking of PDO to isolate repository logic from the DB.
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new ProjectRepository($this->pdo);
    }

    /**
     * Test successful retrieval of service details.
     */
    public function test_get_service_detail_success()
    {
        $serviceData = [
            'id' => 1,
            'name' => 'SEO Analytics Upgrade',
            'client_id' => 10,
            'client_name' => 'Martech solutions',
            'client_email' => 'tech@martech.com',
            'plan_name' => 'Elite Enterprise'
        ];

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("SELECT s.*, u.name as client_name"))
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->stmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($serviceData);

        $result = $this->repository->getServiceDetail(1);

        $this->assertIsArray($result);
        $this->assertEquals('SEO Analytics Upgrade', $result['name']);
        $this->assertEquals(1, $result['id']);
    }

    /**
     * Test that it returns NULL (and not false) when not found, supporting the 
     * stability hardening initiative defined in Phase 1.1.
     */
    public function test_get_service_detail_returns_null_when_not_found()
    {
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with([999]);

        $this->stmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false); // Simulated PDO negative result.

        $result = $this->repository->getServiceDetail(999);

        // Crucial: We expect null, not false.
        $this->assertNull($result);
    }

    /**
     * Test list retrieval for deliverables.
     */
    public function test_get_deliverables_by_service()
    {
        $deliverableList = [
            ['id' => 1, 'title' => 'Initial Report', 'author_name' => 'John'],
            ['id' => 2, 'title' => 'Final Report', 'author_name' => 'John']
        ];

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("SELECT d.*, u.name as author_name"))
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->stmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($deliverableList);

        $result = $this->repository->getDeliverablesByService(1);

        $this->assertCount(2, $result);
        $this->assertEquals('Initial Report', $result[0]['title']);
    }
}
