<?php
namespace App\Middlewares;

use Core\Middleware;
use Core\Auth;
use Core\Session;

/**
 * Require2FaMiddleware - Mandatory 2FA for Admin/Staff
 */
class Require2FaMiddleware implements Middleware
{
    public function handle($params = [])
    {
        if (!Auth::check()) {
            return; // AuthMiddleware will handle it
        }

        $user = Auth::user();
        
        // Only enforce for Admin and Staff
        if (in_array($user['role'], [Auth::ROLE_ADMIN, Auth::ROLE_SUPER_ADMIN, Auth::ROLE_STAFF])) {
            
            // Allow access to logout and profile settings to avoid deadlocks
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $allowedRoutes = [
                'auth/logout',
                'profile/settings',
                'profile/enable2FAStep1',
                'profile/confirm2FA'
            ];

            foreach ($allowedRoutes as $route) {
                if (strpos($uri, $route) !== false) {
                    return;
                }
            }

            if (empty($user['two_factor_enabled'])) {
                // Check database directly just in case the session is stale
                $db = \Core\Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                if (!$stmt->fetchColumn()) {
                    Session::flash('warning', '⚠️ Seguridad Enterprise: Por política de la empresa, es OBLIGATORIO activar la autenticación de dos factores (2FA) para acceder al panel administrativo.');
                    header('Location: ' . url('profile/settings'));
                    exit;
                }
            }
        }
    }
}
