<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Admin Controller - Proxy for admin panel routes
 */
class AdminController extends Controller
{
    protected \Core\Container $container;

    public function __construct(\Core\Container $container)
    {
        $this->middleware('auth');
        $this->middleware('2fa');
        $this->container = $container;

        // Delegamos los permisos granulares a los controladores específicos (UserCMS, Blog, etc.)
        // Solo bloqueamos el acceso transversal a los 'clientes' basales.
        if (Auth::isClient()) {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Default index for AdminController
     */
    public function index()
    {
        $this->redirect('/dashboard');
    }

    /**
     * Alias for users management
     */
    public function user($method = 'index', $id = null)
    {
        return $this->users($method, $id);
    }

    /**
     * Services CMS
     */
    public function services($method = 'index', ...$params)
    {
        $controller = $this->container->get(\App\Controllers\Admin\ServiceCMSController::class);

        // Dynamic method mapping
        $action = $method;
        if ($method === 'edit')
            $action = 'editService';
        if ($method === 'store')
            $action = 'storeService';
        if ($method === 'update')
            $action = 'updateService';
        if ($method === 'delete')
            $action = 'deleteService';

        // Categorías mapping
        if ($method === 'updateCategory')
            $action = 'updateCategory';
        if ($method === 'storeCategory')
            $action = 'storeCategory';
        if ($method === 'storePlan')
            $action = 'storePlan';
        if ($method === 'deletePlan')
            $action = 'deletePlan';
        if ($method === 'reorderPlan')
            $action = 'reorderPlan';

        if (method_exists($controller, $action)) {
            $controller->$action(...$params);
        } else {
            $controller->index();
        }
    }

    /**
     * Blog CMS
     */
    public function blog($method = 'index', $id = null)
    {
        $controller = $this->container->get(\App\Controllers\Admin\BlogCMSController::class);

        // Some methods in BlogCMSController might have different names
        $action = $method;
        if ($action === 'create')
            $action = 'create';
        if ($action === 'edit')
            $action = 'edit';

        if (method_exists($controller, $action)) {
            $controller->$action($id);
        } else {
            $controller->index();
        }
    }

    /**
     * Users Management
     */
    public function users($method = 'index', $id = null)
    {
        $controller = $this->container->get(\App\Controllers\Admin\UserCMSController::class);

        // Alias detail to edit
        $action = $method;
        if ($method === 'detail')
            $action = 'edit';

        if (method_exists($controller, $action)) {
            $controller->$action($id);
        } else {
            $controller->index();
        }
    }

    /**
     * Audit Log
     */
    public function log($method = 'index', ...$params)
    {
        $controller = $this->container->get(\App\Controllers\Admin\LogController::class);
        if (method_exists($controller, $method)) {
            $controller->$method(...$params);
        } else {
            $controller->index();
        }
    }
}
