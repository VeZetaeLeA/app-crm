<?php
namespace Core;

/**
 * TwoFactor - Handles TOTP generation and verification
 */
class TwoFactor
{
    /**
     * Generate a random base32 secret
     */
    public static function generateSecret($length = 16)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verify a TOTP code
     */
    public static function verifyCode($secret, $code, $discrepancy = 1)
    {
        $currentTime = floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $timeStep = $currentTime + $i;
            if (self::calculateTOTP($secret, $timeStep) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate TOTP for a given time step
     */
    private static function calculateTOTP($secret, $timeStep)
    {
        $secretKey = self::base32Decode($secret);

        // Pack time step into 8-byte binary string
        $time = pack('N*', 0) . pack('N*', $timeStep);

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $time, $secretKey, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $otp = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad($otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Helper to decode base32
     */
    private static function base32Decode($base32)
    {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));

        $output = '';
        $v = 0;
        $vbits = 0;

        for ($i = 0, $j = strlen($base32); $i < $j; $i++) {
            $v <<= 5;
            $v += $base32charsFlipped[$base32[$i]];
            $vbits += 5;

            while ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr(($v >> $vbits) & 0xff);
            }
        }

        return $output;
    }

    /**
     * Generate QR code URL (using Google Charts API or similar)
     */
    public static function getQRUrl($userEmail, $secret, $issuer = 'Vezetaelea')
    {
        $label = rawurlencode($issuer . ':' . $userEmail);
        $issuer = rawurlencode($issuer);
        $url = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}";

        return "https://api.qrserver.com/v1/create-qr-code/?data=" . rawurlencode($url) . "&size=200x200&ecc=M";
    }
}
