<?php
namespace Core;

/**
 * RF-08: Motor de Logging Estructurado (JSON) para Observabilidad SaaS
 */
class Log
{
    private static string $logDir = BASE_PATH . '/storage/logs';

    public static function info(string $message, array $context = [])
    {
        self::record('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = [])
    {
        self::record('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = [])
    {
        self::record('ERROR', $message, $context);
    }

    public static function critical(string $message, array $context = [])
    {
        self::record('CRITICAL', $message, $context);
    }

    private static function record(string $level, string $message, array $context)
    {
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0755, true);
        }

        $logData = [
            'timestamp' => date('c'), // ISO 8601 para ELK/SaaS Metrics
            'level'     => $level,
            'message'   => $message,
            'request_id'=> App::$requestId ?? 'system',
            'tenant_id' => Config::get('current_tenant_id', 1),
            'method'    => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'uri'       => $_SERVER['REQUEST_URI'] ?? 'system',
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'context'   => $context
        ];

        // Escritura atómica a archivo JSON diario
        $logFile = self::$logDir . '/app_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Si es crítico, redireccionamos también a SecurityLogger para auditoría persistente (DB)
        if (in_array($level, ['ERROR', 'CRITICAL'])) {
            SecurityLogger::log("SYSTEM_ERROR_{$level}", $message, $level);
        }
    }
}
