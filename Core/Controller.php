<?php
namespace Core;

/**
 * Base Controller Class
 */
abstract class Controller
{
    /**
     * Registered middlewares for this controller
     */
    protected array $middlewares = [];

    /**
     * Register a middleware
     * 
     * @param string $name Middleware name (key in App mapping)
     * @param array $params Parameters for the middleware
     * @param array $only Only run for these methods
     * @param array $except Do not run for these methods
     */
    protected function middleware($name, $params = [], $only = [], $except = [])
    {
        $this->middlewares[] = [
            'name' => $name,
            'params' => $params,
            'only' => $only,
            'except' => $except
        ];
    }

    /**
     * Get all registered middlewares
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * View rendering helper
     */
    protected function view($view, $data = [])
    {
        View::render($view, $data);
    }

    /**
     * View rendering with layout helper
     */
    protected function viewLayout($view, $layout, $data = [])
    {
        View::renderWithLayout($view, $layout, $data);
    }

    /**
     * Redirect helper
     */
    protected function redirect($url)
    {
        if (defined('PHPUNIT_TESTING') && PHPUNIT_TESTING) {
            throw new \Tests\RedirectException(url($url));
        }
        header("Location: " . url($url));
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json($data)
    {
        if (defined('PHPUNIT_TESTING') && PHPUNIT_TESTING) {
            header('Content-Type: application/json');
            echo json_encode($data);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
