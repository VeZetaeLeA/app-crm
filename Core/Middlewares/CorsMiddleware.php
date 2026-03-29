<?php
namespace Core\Middlewares;

use Core\Middleware;
use Core\Config;

/**
 * RF-03: Control de CORS centralizado
 */
class CorsMiddleware implements Middleware
{
    public function handle($params = [])
    {
        $allowedOrigin = Config::get('security.cors_origin', '*');
        
        header("Access-Control-Allow-Origin: $allowedOrigin");
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE, PATCH");
        
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            exit;
        }
    }
}
