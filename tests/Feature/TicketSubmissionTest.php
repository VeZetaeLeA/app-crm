<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Controllers\TicketController;
use Core\Database;
use Core\Session;
use Tests\RedirectException;

class TicketSubmissionTest extends TestCase
{
    private $db;
    private $testEmail;

    protected function setUp(): void
    {
        parent::setUp();
        Session::start();
        $this->db = Database::getInstance()->getConnection();
        $this->testEmail = 'qa_feature_' . time() . '@example.com';
        
        // Ensure clean slate
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup()
    {
        // Delete test client and tickets
        $this->db->prepare("DELETE FROM tickets WHERE subject = 'Feature Test Ticket'")->execute();
        $this->db->prepare("DELETE FROM users WHERE email = ?")->execute([$this->testEmail]);
    }

    /**
     * Test the full Lead-to-Ticket flow
     * - Simulates POST request
     * - Verifies Ticket creation
     * - Verifies Client auto-registration
     * - Verifies Redirect behavior
     */
    public function test_submit_creates_ticket_and_user()
    {
        // 1. Prepare Request Data
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST = [
            'name' => 'QA Feature User',
            'email' => $this->testEmail,
            'subject' => 'Feature Test Ticket',
            'description' => 'This is a test description for feature testing that exceeds ten characters.',
            'service_id' => 1,
            'service_plan_id' => 1
        ];

        // 2. Execute Controller Action
        $controller = new TicketController();
        
        try {
            ob_start();
            $controller->submit();
            ob_get_clean();
        } catch (RedirectException $e) {
            // Expected redirect to /quote/received
            $this->assertStringContainsString('/quote/received', $e->getUrl());
        }

        // 3. Verify Database: Ticket
        $stmt = $this->db->prepare("SELECT * FROM tickets WHERE subject = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['Feature Test Ticket']);
        $ticket = $stmt->fetch();

        $this->assertNotEmpty($ticket, 'The ticket should have been created in the database.');
        $this->assertEquals('open', $ticket['status']);

        // 4. Verify Database: User (Auto-registration)
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$this->testEmail]);
        $user = $stmt->fetch();

        $this->assertNotEmpty($user, 'The user should have been auto-created.');
        $this->assertEquals('client', $user['role']);
        $this->assertEquals($user['id'], $ticket['client_id'], 'The ticket should belong to the new user.');
        
        // 5. Verify Session Auto-login
        $this->assertEquals($user['id'], Session::get('user')['id'] ?? null, 'The user should be auto-logged in after submission.');
    }

    public function test_submit_fails_validation_on_short_description()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'QA',
            'email' => 'invalid-email',
            'subject' => 'Short',
            'description' => 'short',
            'service_id' => 1,
            'service_plan_id' => 1
        ];

        $controller = new TicketController();
        
        try {
            $controller->submit();
            $this->fail('Validation should have triggered a redirect.');
        } catch (RedirectException $e) {
            // Expected redirect back to /ticket/request on validation failure
            $this->assertStringContainsString('/ticket/request', $e->getUrl());
        }
    }
}
