<?php
/**
 * Loader Explícito de .env
 * RF-02: Carga variables de entorno antes de cualquier lógica
 */

class EnvLoader
{
    /**
     * Carga el archivo .env y valida su existencia
     * 
     * @param string $path Ruta absoluta al archivo .env
     * @throws Exception Si el archivo no existe
     */
    public static function load($path)
    {
        if (!file_exists($path)) {
            throw new Exception("FATAL: Archivo .env no encontrado en: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios puros
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Procesar líneas con formato KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);

                $key = trim($key);
                $value = trim($value);

                // Manejar comentarios en la misma línea (inline comments)
                if (strpos($value, '#') !== false) {
                    $value = trim(explode('#', $value)[0]);
                }

                // Remover comillas si existen
                $value = trim($value, '"\'');

                // Inyectar en las tres ubicaciones estándar
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        // RF-01: Validar integridad del sistema
        self::assert(['ENVIRONMENT', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_KEY', 'JWT_SECRET', 'REDIS_HOST']);
        
        $validEnvironments = ['local', 'demo', 'production'];
        $environment = getenv('ENVIRONMENT');

        if (!in_array($environment, $validEnvironments)) {
            throw new Exception("FATAL: ENVIRONMENT '{$environment}' no es válido. Valores permitidos: " . implode(', ', $validEnvironments));
        }
    }

    /**
     * Asegura que las variables críticas existan
     * 
     * @param array $keys
     * @throws Exception
     */
    public static function assert(array $keys)
    {
        $missing = [];
        foreach ($keys as $key) {
            $val = getenv($key);
            if ($val === false || $val === '') {
                // Permitimos DB_PASSWORD vacío solo si es local
                if ($key === 'DB_PASSWORD' && getenv('ENVIRONMENT') === 'local') {
                    continue;
                }
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new Exception("ERROR DE CONFIGURACIÓN: Faltan variables obligatorias en .env: [" . implode(', ', $missing) . "]");
        }
    }

    /**
     * Obtiene una variable de entorno
     * 
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

