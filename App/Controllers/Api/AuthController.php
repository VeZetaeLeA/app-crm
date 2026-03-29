<?php

namespace App\Controllers\Api;

use App\Models\User;
use Core\JWT;
use Core\SecurityLogger;

/**
 * Api Auth Controller - JWT based login
 */
class AuthController extends ApiController
{
    private \App\Repositories\UserRepository $userRepo;

    public function __construct(JWT $jwt, \App\Repositories\UserRepository $userRepo)
    {
        parent::__construct($jwt);
        $this->userRepo = $userRepo;
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error("Method not allowed", 405);
        }

        // Handle both raw JSON and form data
        $body = file_get_contents('php://input');
        $data = json_decode($body, true) ?? $_POST;

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            $this->error("Email and password are required");
        }

        // Using model for complex authentication (which includes password verification)
        $userModel = new User();
        $user = $userModel->authenticate($email, $password);

        if ($user) {
            $token = $this->jwt->encode([
                'user_id' => $user['id'],
                'role' => $user['role'],
                'email' => $user['email']
            ]);

            $refreshToken = $this->jwt->generateRefreshToken($user['id']);

            SecurityLogger::log('api_login_success', ['user_id' => $user['id']]);

            $this->json([
                'success' => true,
                'token' => $token,
                'refresh_token' => $refreshToken,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            SecurityLogger::log('api_login_failed', ['email' => $email], 'WARN');
            $this->error("Invalid credentials", 401);
        }
    }

    /**
     * POST /api/v1/auth/refresh
     */
    public function refresh()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true) ?? $_POST;
        $refreshToken = $data['refresh_token'] ?? '';

        if (!$refreshToken) {
            $this->error("Refresh token is required");
        }

        $userId = $this->jwt->validateRefreshToken($refreshToken);

        if (!$userId) {
            $this->error("Invalid or expired refresh token", 401);
        }

        // Generate new Access Token and ROTATE Refresh Token (RF-02)
        try {
            $user = $this->userRepo->find($userId);

            if (!$user) {
                $this->error("User not found", 404);
            }

            $token = $this->jwt->encode([
                'user_id' => $user['id'],
                'role' => $user['role'],
                'email' => $user['email']
            ]);

            $newRefreshToken = $this->jwt->rotateRefreshToken($refreshToken);

            $this->json([
                'success' => true,
                'token' => $token,
                'refresh_token' => $newRefreshToken
            ]);
        } catch (\Exception $e) {
            $this->error("Refresh failed: " . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/auth/logout
     * RF-02: Revocación de tokens
     */
    public function logout()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true) ?? $_POST;
        $refreshToken = $data['refresh_token'] ?? '';

        if ($refreshToken) {
            $this->jwt->revokeRefreshToken($refreshToken);
        }

        $this->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}

