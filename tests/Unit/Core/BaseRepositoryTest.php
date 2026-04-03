<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Repositories\BaseRepository;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

/**
 * Concrete implementation for testing abstract BaseRepository
 */
class TestRepository extends BaseRepository
{
    protected string $table = 'test_table';
    
    public function publicFetch(string $sql, array $params = []): ?array
    {
        return $this->fetch($sql, $params);
    }

    public function publicFetchAll(string $sql, array $params = []): array
    {
        return $this->fetchAll($sql, $params);
    }
}

class BaseRepositoryTest extends TestCase
{
    private $pdo;
    private $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->repository = new TestRepository($this->pdo);
    }

    public function test_fetch_returns_null_when_pdo_returns_false()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false); // Simulate no record found

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->repository->publicFetch("SELECT * FROM test_table WHERE id = ?", [1]);

        $this->assertNull($result, 'fetch() should return null instead of false when no record is found.');
    }

    public function test_fetch_returns_array_when_pdo_returns_row()
    {
        $mockRow = ['id' => 1, 'name' => 'Test'];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($mockRow);

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->repository->publicFetch("SELECT * FROM test_table WHERE id = ?", [1]);

        $this->assertIsArray($result);
        $this->assertEquals($mockRow, $result);
    }

    public function test_fetchAll_returns_empty_array_when_pdo_returns_false_or_empty()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn([]); // PDO returns empty array for fetchAll usually

        $this->pdo->method('prepare')->willReturn($stmt);

        $result = $this->repository->publicFetchAll("SELECT * FROM test_table");

        $this->assertIsArray($result);
        $this->assertEmpty($result, 'fetchAll() should return an empty array when no records are found.');
    }
}
