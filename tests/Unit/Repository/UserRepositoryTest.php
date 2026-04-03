<?php
declare(strict_types=1);

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Repositories\UserRepository;
use PDO;
use PDOStatement;

/**
 * UserRepositoryTest
 * Validates the security layer and query logic for the user identity system.
 */
class UserRepositoryTest extends TestCase
{
    private $pdo;
    private $stmt;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new UserRepository($this->pdo);

        \Core\Config::set('current_tenant_id', 1);
    }

    /**
     * Test successful user lookup by email.
     */
    public function test_find_by_email_success()
    {
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@ex.com',
            'tenant_id' => 1
        ];

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("SELECT * FROM users WHERE email = ?"))
            ->willReturn($this->stmt);

        // We expect Email and Tenant ID.
        $this->stmt->expects($this->once())
            ->method('execute')
            ->with(['john@ex.com', 1]);

        $this->stmt->expects($this->once())
            ->method('fetch')
            ->willReturn($userData);

        $result = $this->repository->findByEmail('john@ex.com');

        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals(1, $result['id']);
    }

    /**
     * Test that user lookup returns null when not found.
     */
    public function test_find_by_email_returns_null_when_not_found()
    {
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute');

        $this->stmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $result = $this->repository->findByEmail('missing@ex.com');

        $this->assertNull($result);
    }

    /**
     * Test that user creation correctly handles field encryption.
     */
    public function test_create_user_encrypts_phone()
    {
        // Mocking parent::create call structure.
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("INSERT INTO users (name, phone, tenant_id) VALUES (?, ?, ?)"))
            ->willReturn($this->stmt);

        // We expect the phone value to be encrypted before insertion.
        // We'll assert that the value is NOT "123456789" in the second argument.
        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($params) {
                // Return true if the phone (idx 1) is NOT '123456789' due to encryption logic.
                return $params[0] === 'Test User' && $params[1] !== '123456789' && $params[2] === 1;
            }));

        $this->pdo->expects($this->any())
            ->method('lastInsertId')
            ->willReturn("11");

        $id = $this->repository->create([
            'name' => 'Test User',
            'phone' => '123456789'
        ]);

        $this->assertEquals(11, $id);
    }

    /**
     * Test user decryption for secure retrieval.
     */
    public function test_decrypt_user_fields()
    {
        // Encrypted phone mock.
        $encrypted = \Core\Encryption::encrypt('987654321');
        $user = ['id' => 1, 'phone' => $encrypted];

        $decrypted = $this->repository->decryptUser($user);

        $this->assertEquals('987654321', $decrypted['phone']);
    }
}
