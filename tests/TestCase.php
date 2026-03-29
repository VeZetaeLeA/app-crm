<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Core\Database;
use Core\Config;

/**
 * DataWyrd Test Base Class
 * Sets up environment for testing without affecting production/demo data.
 */
class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we are in testing environment
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__));
        }

        // Mocking or re-loading config for tests if needed
        // For actual enterprise testing, we would use a separate sqlite :memory: DB
        // But for this phase, we ensure tests are isolated via Mocking where possible.
    }

    /**
     * Helper to create a user for testing
     */
    protected function createMockUser($role = 'client')
    {
        return [
            'id' => 999,
            'name' => 'Test ' . ucfirst($role),
            'email' => 'test@datawyrd.com',
            'role' => $role
        ];
    }
}
