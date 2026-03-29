<?php
namespace Core;

/**
 * Main App Router Class
 */
class App
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];
    public static string $requestId;
    protected Container $container;

    protected array $globalMiddlewares = [
        'cors' => \Core\Middlewares\CorsMiddleware::class,
        'sanitize' => \Core\Middlewares\SanitizeMiddleware::class,
        'rate_limit' => \Core\Middlewares\RateLimitMiddleware::class,
    ];

    /**
     * Middleware mapping
     */
    protected array $middlewareMap = [
        'auth' => \App\Middlewares\AuthMiddleware::class,
        'role' => \App\Middlewares\RoleMiddleware::class,
        'tenant' => \App\Middlewares\TenantResolverMiddleware::class,
        '2fa' => \App\Middlewares\Require2FaMiddleware::class,
    ];

    public function __construct()
    {
        $this->container = new Container();
        $this->container->instance(Container::class, $this->container);

        // Register PDO in the container
        $db = Database::getInstance()->getConnection();
        $this->container->instance(\PDO::class, $db);

        self::$requestId = bin2hex(random_bytes(8));
        
        // 0. Ejecutar middlewares globales de seguridad (RF-03)
        $this->runGlobalMiddlewares();

        $url = $this->parseUrl();

        // 1. Protección CSRF Global (Obligatoria para métodos que cambian estado)
        $stateChangingMethods = ['POST', 'PUT', 'DELETE', 'PATCH'];
        $isWebhook = isset($url[0]) && strtolower($url[0]) === 'webhook';
        $isApi = isset($url[0]) && strtolower($url[0]) === 'api';

        // Rutas API no requieren CSRF (usan JWT)
        if (in_array($_SERVER['REQUEST_METHOD'], $stateChangingMethods) && !$isWebhook && !$isApi) {
            $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!Validator::verifyCsrfToken($token)) {
                if (Config::get('debug', false)) {
                    $sessionToken = Session::get('csrf_token');
                    $postToken = $_POST['_token'] ?? 'ausente';
                    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'ausente';
                    die("Error: Token CSRF inválido o ausente. <br>Recibido en POST: [$postToken] <br>Recibido en Header: [$headerToken] <br>Esperado en Sesión: [$sessionToken]");
                } else {
                    // En demo/prod, error silencioso (403 Forbidden)
                    header('HTTP/1.1 403 Forbidden');
                    exit('Acceso denegado: Token de seguridad inválido.');
                }
            }
        }

        // 2. Bloquear peticiones de archivos estáticos inexistentes
        if (isset($url[0])) {
            $basePath = strtolower($url[0]);
            if (in_array($basePath, ['assets', 'storage', 'css', 'js', 'images']) || strpos($basePath, '.') !== false) {
                http_response_code(404);
                $this->trigger404();
                exit;
            }
        }

        // 3. Delegation to API Router
        if ($isApi) {
            $apiRouter = $this->container->build(ApiRouter::class);
            $apiRouter->handle($url);
            exit;
        }

        // Check if controller exists in subdirectory (e.g., Admin/LogController)
        if (isset($url[0]) && isset($url[1])) {
            // Try subdirectory first (e.g., admin/logs -> Admin/LogController)
            $subDirPath = BASE_PATH . '/App/Controllers/' . ucfirst($url[0]) . '/' . ucfirst($url[1]) . 'Controller.php';
            if (file_exists($subDirPath)) {
                $this->controller = ucfirst($url[0]) . '\\' . ucfirst($url[1]) . 'Controller';
                unset($url[0]);
                unset($url[1]);
            }
        }

        // If not found in subdirectory, check main Controllers directory
        if ($this->controller === 'HomeController' && isset($url[0])) {
            if (file_exists(BASE_PATH . '/App/Controllers/' . ucfirst($url[0]) . 'Controller.php')) {
                $this->controller = ucfirst($url[0]) . 'Controller';
                unset($url[0]);
            } else {
                // Controller not found - 404
                $this->trigger404();
                return;
            }
        }

        $controllerPath = "\\App\\Controllers\\" . $this->controller;
        $this->controller = $this->container->get($controllerPath);

        // Check if method exists
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                // Method explicitly requested but not found
                $this->trigger404();
                return;
            }
        } elseif (isset($url[2])) {
            // For subdirectory controllers, method might be in position 2
            if (method_exists($this->controller, $url[2])) {
                $this->method = $url[2];
                unset($url[2]);
            } else {
                $this->trigger404();
                return;
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Run the controller method
        $this->processMiddlewares();

        // Use container to call method (supports DI in methods too)
        $this->container->call($this->controller, $this->method, $this->params);
    }

    protected function runGlobalMiddlewares()
    {
        foreach ($this->globalMiddlewares as $name => $class) {
            $mw = new $class();
            $mw->handle();
        }
    }

    /**
     * Process middlewares registered in the controller
     */
    protected function processMiddlewares()

    {
        if (!method_exists($this->controller, 'getMiddlewares')) {
            return;
        }

        $middlewares = $this->controller->getMiddlewares();

        foreach ($middlewares as $mw) {
            $name = $mw['name'];
            $params = $mw['params'];
            $only = $mw['only'];
            $except = $mw['except'];

            // Filter by method names
            if (!empty($only) && !in_array($this->method, $only)) {
                continue;
            }
            if (!empty($except) && in_array($this->method, $except)) {
                continue;
            }

            // Resolve and run middleware
            if (isset($this->middlewareMap[$name])) {
                $middlewareClass = $this->middlewareMap[$name];
                $middleware = new $middlewareClass();
                $middleware->handle($params);
            }
        }
    }

    private function trigger404()
    {
        $controller = $this->container->get(\App\Controllers\ErrorController::class);
        $this->container->call($controller, 'notFound');
    }

    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
}
