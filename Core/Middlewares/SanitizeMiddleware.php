<?php
namespace Core\Middlewares;

use Core\Middleware;

/**
 * RF-03: Sanitización recursiva de inputs (prevención XSS)
 */
class SanitizeMiddleware implements Middleware
{
    public function handle($params = [])
    {
        $_GET = $this->sanitize($_GET);
        $_POST = $this->sanitize($_POST);
        $_REQUEST = $this->sanitize($_REQUEST);
        
        // El body JSON se suele leer en controladores directamente, 
        // pero podemos interceptar la lectura global si fuera necesario.
    }

    private function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } elseif (is_string($data)) {
            // Limpieza básica de HTML tags y conversión de caracteres especiales
            $data = trim($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
}
