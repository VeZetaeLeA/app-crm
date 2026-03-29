<?php
namespace Core;

/**
 * Rate Limiter - Prevents rapid successive requests from a single IP
 */
class RateLimiter
{
    private static $storageDir = BASE_PATH . '/storage/rates/';

    /**
     * Increment an attempt and check if it shouldn't be blocked.
     * Returns true if request is allowed, false if it's rate-limited/blocked.
     */
    public static function attempt($key, $limit, $period, $lockoutTime = null)
    {
        if (!is_dir(self::$storageDir)) {
            mkdir(self::$storageDir, 0777, true);
        }

        $file = self::$storageDir . md5($key) . '.json';
        $data = ['requests' => [], 'blocked_until' => 0];

        if (file_exists($file)) {
            $decoded = json_decode(file_get_contents($file), true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $now = time();

        if ($data['blocked_until'] > $now) {
            return false;
        }

        // Clean old requests
        $data['requests'] = array_filter($data['requests'], function ($timestamp) use ($now, $period) {
            return $timestamp > ($now - $period);
        });

        // Add current request
        $data['requests'][] = $now;

        // Check against limit
        if (count($data['requests']) > $limit) {
            // Block
            $blockFor = $lockoutTime ?? $period;
            $data['blocked_until'] = $now + $blockFor;
            file_put_contents($file, json_encode($data));
            return false;
        }

        file_put_contents($file, json_encode($data));
        return true;
    }

    /**
     * Clear the rate limiter attempts, useful upon successful login.
     */
    public static function clear($key)
    {
        $file = self::$storageDir . md5($key) . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Legacy method for global requests. Retained for backwards compatibility inside App.php or index.php
     */
    public static function check($limit = 60, $period = 60)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!self::attempt($ip, $limit, $period)) {
            header('HTTP/1.1 429 Too Many Requests');
            header('Retry-After: ' . $period);
            exit('Límite de peticiones excedido. Protección activada.');
        }
    }
}
