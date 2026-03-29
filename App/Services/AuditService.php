<?php
namespace App\Services;

use Core\Database;
use Core\Auth;

/**
 * Audit Service
 * Servicio centralizado para auditoría y logging de acciones críticas
 */
class AuditService
{
    /**
     * Registra una acción en el log de auditoría
     */
    public static function log(string $action, array $details = [], string $level = 'INFO'): void
    {
        // Redirigir al SecurityLogger centralizado del Core para consistencia total
        \Core\SecurityLogger::log($action, $details, $level);
    }

    /**
     * Escribe en archivo de log
     */
    private static function writeToFile(string $action, array $details, string $level, string $user, string $ip, ?string $error = null): void
    {
        $logFile = BASE_PATH . '/storage/logs/audit.log';
        $timestamp = date('Y-m-d H:i:s');

        $logEntry = "[$timestamp] [$level] ACTION: $action | USER: $user | IP: $ip\n";
        $logEntry .= "DETAILS: " . json_encode($details, JSON_PRETTY_PRINT) . "\n";

        if ($error) {
            $logEntry .= "ERROR: $error\n";
        }

        $logEntry .= str_repeat('-', 80) . "\n";

        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    // Static versions of the helper methods
    public static function logLogin(array $user): void
    {
        self::log('login_success', ['user_id' => $user['id'], 'email' => $user['email'], 'role' => $user['role']], 'INFO');
    }
    public static function logLoginFailed(string $email): void
    {
        self::log('login_failed', ['email' => $email], 'WARN');
    }
    public static function logLogout(): void
    {
        self::log('logout', [], 'INFO');
    }
    public static function logPasswordChange(int $userId): void
    {
        self::log('password_changed', ['user_id' => $userId], 'INFO');
    }
    public static function logUserCreated(int $userId, string $role): void
    {
        self::log('user_created', ['user_id' => $userId, 'role' => $role], 'INFO');
    }
    public static function logUserDeleted(int $userId): void
    {
        self::log('user_deleted', ['user_id' => $userId], 'WARN');
    }
    public static function logAccessDenied(string $resource, string $action): void
    {
        self::log('access_denied', ['resource' => $resource, 'action' => $action], 'WARN');
    }
    public static function logError(string $message, array $context = []): void
    {
        self::log('application_error', array_merge(['message' => $message], $context), 'ERROR');
    }

    /**
     * Obtiene logs de auditoría con filtros
     */
    public static function getLogs(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM audit_logs WHERE 1=1";
        $params = [];

        if (isset($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (isset($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }
        if (isset($filters['level'])) {
            $sql .= " AND level = ?";
            $params[] = $filters['level'];
        }
        if (isset($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (isset($filters['date_to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene estadísticas de auditoría
     */
    public static function getStats(string $period = 'today'): array
    {
        $db = Database::getInstance()->getConnection();
        $dateCondition = match ($period) {
            'today' => "DATE(created_at) = CURDATE()",
            'week' => "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            'month' => "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            default => "1=1"
        };

        $total = $db->query("SELECT COUNT(*) as total FROM audit_logs WHERE $dateCondition")->fetchColumn();
        $byLevel = $db->query("SELECT level, COUNT(*) as count FROM audit_logs WHERE $dateCondition GROUP BY level")->fetchAll();
        $byAction = $db->query("SELECT action, COUNT(*) as count FROM audit_logs WHERE $dateCondition GROUP BY action ORDER BY count DESC LIMIT 10")->fetchAll();
        $byUser = $db->query("SELECT user_email, COUNT(*) as count FROM audit_logs WHERE $dateCondition AND user_id IS NOT NULL GROUP BY user_email ORDER BY count DESC LIMIT 10")->fetchAll();

        return [
            'total' => $total,
            'by_level' => $byLevel,
            'by_action' => $byAction,
            'by_user' => $byUser
        ];
    }
}
