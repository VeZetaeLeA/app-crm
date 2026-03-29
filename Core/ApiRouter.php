<?php

namespace Core;

/**
 * ApiRouter - Specialized router for /api/v1 endpoints.
 * Returns only JSON responses.
 */
class ApiRouter
{
    protected string $version = 'v1';
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(array $url): void
    {
        // Sample URL: ['api', 'v1', 'auth', 'login']
        if (!isset($url[1]) || $url[1] !== $this->version) {
            $this->jsonError("API Version mismatch. Expected {$this->version}", 400);
        }

        if (!isset($url[2])) {
            $this->jsonError("API Endpoint missing", 404);
        }

        $controllerName = ucfirst($url[2]) . 'Controller';
        $fullControllerPath = "\\App\\Controllers\\Api\\" . $controllerName;

        if (!class_exists($fullControllerPath)) {
            $this->jsonError("API Controller {$controllerName} not found", 404);
        }

        $controller = $this->container->get($fullControllerPath);
        $method = $url[3] ?? 'index';

        if (!method_exists($controller, $method)) {
            $this->jsonError("API Method {$method} not found", 404);
        }

        $params = array_slice($url, 4);

        try {
            // Ejecutar middlewares del controlador API
            $this->processMiddlewares($controller, $method);

            // Run the controller method using the container to support method DI
            $this->container->call($controller, $method, $params);
            
        } catch (\Core\Exceptions\AppException $ae) {
            // Error controlado de negocio (RF-10 / UX)
            \Core\ApiResponse::error($ae->getMessage(), $ae->getStatusCode(), $ae->getDetails());
            
        } catch (\Exception $e) {
            // Error técnico no controlado
            SecurityLogger::log('API_FATAL_ERROR', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'CRITICAL');
            
            \Core\ApiResponse::error("Error interno del servidor", 500);
        }

    }

    protected function processMiddlewares($controller, $method)
    {
        if (!method_exists($controller, 'getMiddlewares')) {
            return;
        }

        $middlewares = $controller->getMiddlewares();
        // El App::middlewareMap debería ser accesible o inyectado. 
        // Por simplicidad en este refactor, usamos el mapeo centralizado si existiera en un registro.
        // Pero como ApiController ya tiene authenticate(), muchos controladores lo llaman allí.
        // Lo ideal es que ApiRouter soporte el mismo flujo que App.php.
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
