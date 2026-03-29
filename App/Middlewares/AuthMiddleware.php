<?php
namespace App\Middlewares;

use Core\Middleware;
use Core\Auth;
use Core\Session;

/**
 * Validates that a user is authenticated
 */
class AuthMiddleware implements Middleware
{
    public function handle($params = [])
    {
        if (!Auth::check()) {
            Session::flash('error', 'Debes iniciar sesión para acceder a esta sección.');
            header('Location: ' . url('auth/login'));
            exit;
        }

        // Multitenancy validation (Enterprise Security)
        $user = Auth::user();
        $currentTenantId = (int) \Core\Config::get('current_tenant_id', 1);

        // SuperAdmin can access any tenant for management purposes
        if ($user['role'] === Auth::ROLE_SUPER_ADMIN) {
            return;
        }

        if ((int) $user['tenant_id'] !== $currentTenantId) {
            \Core\SecurityLogger::log('tenant_access_violation_attempt', [
                'user_id' => $user['id'],
                'user_tenant' => $user['tenant_id'],
                'requested_tenant' => $currentTenantId
            ], 'CRITICAL');

            Session::destroy();
            Session::flash('error', 'Acceso denegado: El dominio o empresa no coincide con tu registro.');
            header('Location: ' . url('auth/login'));
            exit;
        }
    }
}
