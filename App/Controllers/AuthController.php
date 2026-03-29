<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Auth;
use App\Models\User;

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Show Login Page
     */
    public function login()
    {
        if (Auth::check()) {
            $this->redirectByRole();
        }
        $this->viewLayout('public/login', 'public', ['title' => 'Iniciar Sesión | ' . \Core\Config::get('business.company_name')]);
    }

    /**
     * Handle Login Request
     */
    public function doLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // 1. IP Rate Limiting
        $maxAttempts = \Core\Config::get('security.auth_max_attempts', 5);
        $decay = \Core\Config::get('security.auth_rate_limit_decay', 60);
        if (!\Core\RateLimiter::attempt('login_ip_' . $ip, $maxAttempts, $decay)) {
            $this->logLoginAttempt(null, $email, $ip, false);
            Session::flash('error', 'Límite de peticiones excedido. Espera un momento.');
            $this->redirect('/auth/login');
        }

        // 2. User Brute Force Protection (Account Lockout)
        $accountLock = \Core\Config::get('security.auth_account_lock', 900);
        if (!\Core\RateLimiter::attempt('login_user_' . $email, 5, 900, $accountLock)) {
            $this->logLoginAttempt(null, $email, $ip, false);
            Session::flash('error', 'Cuenta temporalmente bloqueada por seguridad tras múltiples intentos.');
            $this->redirect('/auth/login');
        }

        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // Success: clear rate limiters
            \Core\RateLimiter::clear('login_ip_' . $ip);
            \Core\RateLimiter::clear('login_user_' . $email);
            $this->logLoginAttempt($user['id'], $email, $ip, true);

            // Check if 2FA is enabled
            if (!empty($user['two_factor_enabled'])) {
                Session::set('auth_pending_user', $user);
                $this->redirect('/auth/twoFactor');
                return;
            }

            // Standard login
            $this->completeLogin($user);
        } else {
            \Core\SecurityLogger::log('login_failed', ['email' => $email], 'WARN');
            $this->logLoginAttempt(null, $email, $ip, false);
            Session::flash('error', 'Credenciales incorrectas o cuenta inactiva.');
            $this->redirect('/auth/login');
        }
    }

    /**
     * Log the login attempt to Database
     */
    private function logLoginAttempt($userId, $email, $ip, $success)
    {
        try {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $db = \Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO login_logs (user_id, ip_address, email_attempted, success, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $ip, $email, $success ? 1 : 0, $userAgent]);
        } catch (\PDOException $e) {
            // Fail silently or log to error file, don't crash the login
            error_log("Failed to insert login_logs: " . $e->getMessage());
        }
    }

    /**
     * Show 2FA Challenge Page
     */
    public function twoFactor()
    {
        if (!Session::has('auth_pending_user')) {
            $this->redirect('/auth/login');
        }
        $this->viewLayout('public/auth/2fa', 'public', ['title' => 'Verificación 2FA | ' . \Core\Config::get('business.company_name')]);
    }

    /**
     * Verify 2FA Code
     */
    public function verify2FA()
    {
        $user = Session::get('auth_pending_user');
        $code = $_POST['code'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!$user) {
            $this->redirect('/auth/login');
        }

        // User Brute Force Protection matching login
        $accountLock = \Core\Config::get('security.auth_account_lock', 900);
        if (!\Core\RateLimiter::attempt('login_user_' . $user['email'], 5, 900, $accountLock)) {
            $this->logLoginAttempt($user['id'], $user['email'], $ip, false);
            Session::flash('error', 'Cuenta temporalmente bloqueada por seguridad tras múltiples intentos.');
            $this->redirect('/auth/login');
        }

        if (\Core\TwoFactor::verifyCode($user['two_factor_secret'], $code)) {
            \Core\RateLimiter::clear('login_user_' . $user['email']);
            Session::remove('auth_pending_user');
            $this->completeLogin($user);
        } else {
            \Core\SecurityLogger::log('2fa_failed', ['user_id' => $user['id']], 'WARN');
            $this->logLoginAttempt($user['id'], $user['email'], $ip, false);
            Session::flash('error', 'Código de seguridad incorrecto.');
            $this->redirect('/auth/twoFactor');
        }
    }

    /**
     * Complete Login Process
     */
    private function completeLogin($user)
    {
        // Regenerar ID de sesión para prevenir Session Fixation
        session_regenerate_id(true);

        Session::set('user', $user);
        \Core\SecurityLogger::log('login_success', 'Usuario inició sesión correctamente.');
        $this->redirectByRole();
    }

    /**
     * Logout
     */
    public function logout()
    {
        \Core\SecurityLogger::log('logout', 'Usuario cerró sesión.');
        Session::destroy();
        $this->redirect('/');
    }

    /**
     * Redirect Based on Role
     */
    private function redirectByRole()
    {
        // Simplemente redirigir al dashboard, el DashboardController se encarga del rol
        $this->redirect('/dashboard');
    }
}
