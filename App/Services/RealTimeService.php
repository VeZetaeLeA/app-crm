<?php
namespace App\Services;

use Predis\Client as RedisClient;

class RealTimeService
{
    private static $client;

    private static function getClient()
    {
        if (!self::$client) {
            $host = getenv('REDIS_HOST') ?: '127.0.0.1';
            $port = getenv('REDIS_PORT') ?: '6379';
            self::$client = new RedisClient([
                'scheme' => 'tcp',
                'host'   => $host,
                'port'   => $port,
            ]);
        }
        return self::$client;
    }

    /**
     * Broadcast a message to all users
     */
    public static function broadcast(string $type, array $data)
    {
        $payload = json_encode([
            'type' => $type,
            'data' => $data,
            'timestamp' => time()
        ]);
        
        try {
            self::getClient()->publish('Vezetaelea-realtime', $payload);
            return true;
        } catch (\Exception $e) {
            error_log("RealTime Broadcast Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send message to a specific user
     */
    public static function sendToUser(int $userId, string $type, array $data)
    {
        $payload = json_encode([
            'type' => $type,
            'target_user' => $userId,
            'data' => $data,
            'timestamp' => time()
        ]);

        try {
            self::getClient()->publish('Vezetaelea-realtime', $payload);
            return true;
        } catch (\Exception $e) {
            error_log("RealTime User Send Error: " . $e->getMessage());
            return false;
        }
    }
}
