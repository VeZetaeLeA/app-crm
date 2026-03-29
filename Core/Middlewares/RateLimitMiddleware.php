<?php
namespace Core\Middlewares;

use Core\Middleware;
use Core\RateLimiter;
use Core\Config;

/**
 * RF-03: Rate Limiting middleware
 */
class RateLimitMiddleware implements Middleware
{
    public function handle($params = [])
    {
        $limit = $params['limit'] ?? Config::get('security.global_rate_limit', 100);
        $period = $params['period'] ?? Config::get('security.global_rate_limit_window', 60);

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = "rl_" . $ip;

        if (!RateLimiter::attempt($key, $limit, $period)) {
            header('HTTP/1.1 429 Too Many Requests');
            header('Content-Type: application/json');
            
            echo json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'retry_after' => $period
            ]);
            exit;
        }
    }
}
