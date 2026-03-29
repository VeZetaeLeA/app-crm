<?php
namespace Core;

/**
 * Session Management Class
 */
class Session
{
    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            // Configuración de seguridad para cookies
            $secure = Config::get('force_https', false); // Basado en el entorno (demo/prod)

            session_set_cookie_params([
                'lifetime' => Config::get('security.session_lifetime', 7200),
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_set_save_handler(new DatabaseSessionHandler(), true);
            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public static function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function flash($key, $message = null)
    {
        if ($message) {
            self::set("flash_$key", $message);
        } else {
            $msg = self::get("flash_$key");
            self::remove("flash_$key");
            return $msg;
        }
    }
}
