<?php

namespace App\Controllers\Api;

use Core\SecurityLogger;
use Core\JWT;
use Core\App;

/**
 * ApiController - Base class for API controllers
 */
abstract class ApiController
{
    protected JWT $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Standard JSON response helper (RF-10) delegated to Core\ApiResponse
     */
    protected function json(array $data, int $code = 200, string $message = 'Success'): void
    {
        \Core\ApiResponse::success($data, $message, $code);
    }

    /**
     * Error JSON response helper (RF-10) delegated to Core\ApiResponse
     */
    protected function error(string $message, int $code = 400, array $details = []): void
    {
        SecurityLogger::log('api_response_error', [
            'code' => $code,
            'message' => $message,
            'request' => $_SERVER['REQUEST_URI']
        ], 'WARN');

        \Core\ApiResponse::error($message, $code, $details);
    }



    /**
     * JWT Authentication Middleware
     */
    protected function authenticate(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            $this->error("Missing or invalid Authorization header", 401);
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwt->decode($token);

        if (!$payload) {
            $this->error("Invalid or expired token", 401);
        }

        return $payload;
    }
}
