<?php
namespace Core;

use Exception;

/**
 * Encryption Utility for Sensitive Data
 */
class Encryption
{
    private static string $cipher = 'AES-256-CBC';

    /**
     * Encrypt a string.
     */
    public static function encrypt(string $value): string
    {
        $key = Config::get('app_key');
        if (strlen($key) < 32) {
            throw new Exception("Encryption key must be at least 32 characters.");
        }

        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encrypted = openssl_encrypt($value, self::$cipher, $key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a string.
     */
    public static function decrypt(string $payload): ?string
    {
        $key = Config::get('app_key');
        $payload = base64_decode($payload);

        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($payload, 0, $ivLength);
        $encrypted = substr($payload, $ivLength);

        $decrypted = openssl_decrypt($encrypted, self::$cipher, $key, 0, $iv);

        return $decrypted ?: null;
    }
}
