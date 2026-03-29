<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use Core\Database;
use Core\TwoFactor;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show Profile/Security Settings
     */
    public function settings()
    {
        $db = Database::getInstance()->getConnection();
        $user = Auth::user();

        $stmt = $db->prepare("SELECT two_factor_enabled, two_factor_secret FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch();

        $this->viewLayout('shared/profile/settings', Auth::role(), [
            'title' => 'Configuración de Seguridad | ' . \Core\Config::get('business.company_name'),
            'user' => $userData
        ]);
    }

    /**
     * Enable 2FA - Step 1: Generate Secret
     */
    public function enable2FAStep1()
    {
        $secret = TwoFactor::generateSecret();
        Session::set('temp_2fa_secret', $secret);

        $qrUrl = TwoFactor::getQRUrl(Auth::user()['email'], $secret);

        $this->json([
            'success' => true,
            'secret' => $secret,
            'qrUrl' => $qrUrl
        ]);
    }

    /**
     * Enable 2FA - Step 2: Verify & Confirm
     */
    public function confirm2FA()
    {
        $code = $_POST['code'] ?? '';
        $secret = Session::get('temp_2fa_secret');

        if (!$secret) {
            $this->json(['success' => false, 'message' => 'Sesión de configuración expirada.']);
            return;
        }

        if (TwoFactor::verifyCode($secret, $code)) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET two_factor_secret = ?, two_factor_enabled = 1 WHERE id = ?");
            $stmt->execute([$secret, Auth::user()['id']]);

            Session::remove('temp_2fa_secret');
            \Core\SecurityLogger::log('2fa_enabled', 'Usuario activó la autenticación de dos factores.');

            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Código inválido. Intenta de nuevo.']);
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2FA()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET two_factor_enabled = 0, two_factor_secret = NULL WHERE id = ?");
        $stmt->execute([Auth::user()['id']]);

        \Core\SecurityLogger::log('2fa_disabled', 'Usuario desactivó la autenticación de dos factores.', 'WARN');

        $this->redirect('/profile/settings');
    }

    /**
     * Update Password
     */
    public function updatePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile/settings');
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($password) || strlen($password) < 6) {
            Session::flash('error', 'La contraseña debe tener al menos 6 caracteres.');
            $this->redirect('/profile/settings#change-password');
            return;
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Las contraseñas no coinciden.');
            $this->redirect('/profile/settings#change-password');
            return;
        }

        $db = Database::getInstance()->getConnection();
        $hashed = Auth::hashPassword($password);
        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$hashed, Auth::user()['id']]);

        if ($result) {
            \Core\SecurityLogger::log('password_changed', 'Usuario actualizó su contraseña.');
            Session::flash('success', 'Contraseña actualizada con éxito.');
        } else {
            Session::flash('error', 'Error al actualizar la contraseña.');
        }

        $this->redirect('/profile/settings');
    }
}
