<?php
/**
 * Global Helper Functions for VeZetaeLeA CRM
 */

use Core\Config;
use Core\Validator;
use Core\Lang;
use Core\Auth;

/**
 * Helper: Generates an absolute URL based on the dynamic environment configuration.
 *
 * @param string $path The relative path to append.
 * @return string The absolute URL.
 */
if (!function_exists('url')) {
    function url($path = '')
    {
        // If path is already an absolute URL (starts with http or https), return it directly
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $baseUrl = rtrim(Config::get('base_url', 'http://localhost/app-crm'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

/**
 * Helper: Obtiene la ruta absoluta desde la raíz del proyecto.
 */
if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

/**
 * Helper: Returns the absolute file path to the public directory.
 *
 * @param string $path The relative path to append.
 * @return string The absolute file system path.
 */
if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        return BASE_PATH . '/public' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

/**
 * Helper: Generates a CSRF token hidden input field for forms.
 *
 * @return string HTML string containing the hidden input.
 */
if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        $token = Validator::generateCsrfToken();
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

/**
 * Helper: Retrieves the active services for the currently authenticated client.
 *
 * @return array List of active services or an empty array if not authenticated.
 */
if (!function_exists('getActiveServices')) {
    function getActiveServices()
    {
        if (!Auth::check())
            return [];
        return \App\Models\Service::getActiveByClient(Auth::user()['id']);
    }
}

/**
 * Helper: Translates system status codes into human-readable Spanish text.
 *
 * @param string $status The internal status code.
 * @return string The translated status label.
 */
if (!function_exists('translateStatus')) {
    function translateStatus($status)
    {
        $translated = __('status.' . $status);
        
        // Fallback if key doesn't exist (returns [Missing Lang: ...])
        if (strpos($translated, 'status.') !== false) {
            return str_replace('_', ' ', ucfirst($status));
        }

        return $translated;
    }
}

/**
 * Helper: Translation (i18n) Dot-Notation Accessor.
 *
 * @param string $key The translation key (e.g. 'home.hero.title')
 * @param array $placeholders Dynamic values to replace.
 * @return string The translated text.
 */
if (!function_exists('__')) {
    function __($key, $placeholders = [])
    {
        return Lang::get($key, $placeholders);
    }
}
