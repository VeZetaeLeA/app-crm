<?php
namespace App\Middlewares;

use Core\Middleware;
use Core\Auth;
use Core\Session;

/**
 * Validates user roles (RBAC)
 */
class RoleMiddleware implements Middleware
{
    /**
     * @param array $params Array of allowed roles
     */
    public function handle($params = [])
    {
        if (!Auth::check()) {
            header('Location: ' . url('auth/login'));
            exit;
        }

        $userRole = Auth::role();

        if (!empty($params) && !in_array($userRole, $params)) {
            Session::flash('error', 'Acceso denegado: No tienes los permisos necesarios.');
            header('Location: ' . url());
            exit;
        }
    }
}
