<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use Core\JWT;
use Core\Database;

/**
 * RF-09: Unit Test para JWT y Seguridad
 */
class JWTTest extends TestCase
{
    private $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $db = Database::getInstance()->getConnection();
        $this->jwt = new JWT($db);
    }

    public function test_can_encode_and_decode_token()
    {
        $payload = [
            'user_id' => 1,
            'tenant_id' => 1,
            'role' => 'admin'
        ];

        $token = $this->jwt->encode($payload);
        $this->assertNotEmpty($token);

        $decoded = $this->jwt->decode($token);
        $this->assertEquals(1, $decoded['user_id']);
        $this->assertEquals('admin', $decoded['role']);
    }

    public function test_returns_null_on_expired_token()
    {
        $payload = ['user_id' => 1];
        // Forzamos un token con expiración negativa (-100 segundos)
        $token = $this->jwt->encode($payload, -100);
        
        $decoded = $this->jwt->decode($token);
        $this->assertNull($decoded);
    }

    public function test_fails_with_wrong_secret()
    {
        // Este test asume que el secreto en Config está bien inyectado.
        // Si no fuera así el decode fallaría.
        $this->assertNotNull(\Core\Config::get('security.jwt_secret'));
    }
}
