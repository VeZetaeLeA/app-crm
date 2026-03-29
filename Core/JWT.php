<?php

namespace Core;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Exception;

/**
 * JWT Wrapper for Vezetaelea 9.5
 */
class JWT
{
    private \PDO $db;
    private string $secret;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->secret = Config::get('security.jwt_secret', 'Vezetaelea-default-secret');
    }

    /**
     * Encode payload into a JWT string.
     */
    public function encode(array $payload, int $expiry = 3600): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;

        return FirebaseJWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Decode JWT string into a payload array.
     */
    public function decode(string $token): ?array
    {
        try {
            // El secreto DEBE venir de Config y no tener fallbacks internos peligrosos (RNF-01)
            $decoded = FirebaseJWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            SecurityLogger::log('JWT_DECODE_FAILED', [
                'error' => $e->getMessage(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ], 'WARN');
            return null;
        }
    }

    /**
     * Generate a random refresh token and store it.
     */
    public function generateRefreshToken(int $userId, int $days = 30): string
    {
        $token = bin2hex(random_bytes(32));
        $expirySeconds = Config::get('security.refresh_token_ttl', $days * 86400);
        $expiresAt = date('Y-m-d H:i:s', time() + $expirySeconds);

        $stmt = $this->db->prepare("INSERT INTO jwt_refresh_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $token, $expiresAt]);

        return $token;
    }

    /**
     * Validate a refresh token and return the user_id if valid.
     * RF-02: Incluye detección de re-uso si ya fue reemplazado.
     */
    public function validateRefreshToken(string $token): ?int
    {
        $stmt = $this->db->prepare("SELECT user_id, revoked_at, replaced_by_token, expires_at FROM jwt_refresh_tokens WHERE token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) return null;

        // Detección de re-uso (Critical Security Alert)
        if ($row['revoked_at'] !== null || $row['replaced_by_token'] !== null) {
            SecurityLogger::log('JWT_REFRESH_REUSE_ATTEMPT', [
                'token' => substr($token, 0, 8) . '...',
                'user_id' => $row['user_id']
            ], 'CRITICAL');
            
            // Revocamos todos los tokens del usuario por sospecha de robo de sesión
            $this->revokeUserTokens($row['user_id']);
            return null;
        }

        // Validación de expiración
        if (strtotime($row['expires_at']) < time()) {
            return null;
        }

        return (int) $row['user_id'];
    }

    /**
     * Implementa Rotación de Refresh Tokens (RF-02)
     */
    public function rotateRefreshToken(string $oldToken): string
    {
        $userId = $this->validateRefreshToken($oldToken);
        if (!$userId) {
            throw new Exception("Invalid refresh token for rotation");
        }

        $newToken = $this->generateRefreshToken($userId);

        // Marcamos el anterior como reemplazado
        $stmt = $this->db->prepare("UPDATE jwt_refresh_tokens SET replaced_by_token = ?, revoked_at = NOW() WHERE token = ?");
        $stmt->execute([$newToken, $oldToken]);

        return $newToken;
    }

    /**
     * Revoca un token específico
     */
    public function revokeRefreshToken(string $token): void
    {
        $stmt = $this->db->prepare("UPDATE jwt_refresh_tokens SET revoked_at = NOW(), revoked_by_ip = ? WHERE token = ?");
        $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $token]);
    }

    /**
     * Revoca todos los tokens de un usuario (RF-02)
     */
    public function revokeUserTokens(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE jwt_refresh_tokens SET revoked_at = NOW(), revoked_by_ip = ? WHERE user_id = ? AND revoked_at IS NULL");
        $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $userId]);
    }
}

