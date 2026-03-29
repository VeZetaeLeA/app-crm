<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Database Singleton Class
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // RNF-03: Usar Config::get() centralizado - Sin fallbacks locales para forzar configuración vía .env
        $host = Config::get('db.host');
        $db = Config::get('db.name');
        $user = Config::get('db.user');
        $pass = Config::get('db.pass');

        if (!$host || !$db) {
            throw new PDOException("FATAL: Configuración de base de datos incompleta. Revisa las variables de entorno DB_*");
        }

        // MySQL charset (utf8mb4 para soporte completo de Unicode)
        $charset = 'utf8mb4';
        $collation = 'utf8mb4_unicode_ci';

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        // RNF-04: Opciones robustas de PDO (MANDATORIO)
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$collation}"
        ];

        // RNF-04: Try/catch con mensaje descriptivo (MANDATORIO)
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            $environment = getenv('ENVIRONMENT');
            $errorDetails = sprintf(
                "Database connection failed\nHost: %s\nDatabase: %s\nUser: %s\nError: %s",
                $host,
                $db,
                $user,
                $e->getMessage()
            );

            // Logging obligatorio
            error_log($errorDetails);

            // Relanzar con mensaje claro
            if ($environment === 'local' || $environment === 'demo') {
                throw new PDOException("DB Connection Error: " . $e->getMessage() . "\n\nDetails:\nHost: {$host}\nDatabase: {$db}\nUser: {$user}", (int) $e->getCode());
            } else {
                throw new PDOException("Database connection failed. Please contact support.", (int) $e->getCode());
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
