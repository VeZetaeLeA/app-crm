<?php

namespace Core;

/**
 * SecurityLogger - Enterprise Grade Structured Logging (Vezetaelea 9.5)
 */
class SecurityLogger
{
    /**
     * Log a security or system event.
     * Records to Database for auditing and to JSON File for observability.
     * 
     * @param string $action The action performed (e.g., 'login_success')
     * @param array|string $details Additional details
     * @param string $severity INFO, WARN, ERROR, CRITICAL
     */
    public static function log(string $action, $details = [], string $severity = 'INFO'): void
    {
        try {
            $user = Auth::check() ? Auth::user() : null;
            $userId = $user ? $user['id'] : null;
            $route = $_SERVER['REQUEST_URI'] ?? 'CLI';
            $timestamp = date('Y-m-d H:i:s');
            $requestId = App::$requestId ?? 'system';

            $logData = [
                'request_id' => $requestId,
                'timestamp' => $timestamp,
                'action' => str_replace(' ', '_', strtolower($action)),
                'severity' => strtoupper($severity),
                'user_id' => $userId,
                'user_email' => $user ? $user['email'] : 'guest',
                'route' => $route,
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'details' => $details
            ];

            // 1. Audit Log (Database) - Permanent Record
            self::logToDatabase($logData);

            // 2. Structured JSON Log (File) - Observability/Scaling
            self::logToFile($logData);

            // 3. Immutable External Storage - Enterprise Security
            if (in_array(strtoupper($severity), ['WARN', 'ERROR', 'CRITICAL']) || \Core\Config::get('security.immutable_all_logs', false)) {
                self::logToImmutableStorage($logData);
            }

        } catch (\Exception $e) {
            error_log("CRITICAL: Failed to log security event: " . $e->getMessage());
        }
    }

    private static function logToDatabase(array $data): void
    {
        try {
            $db = Database::getInstance()->getConnection();

            // Get previous hash for cryptographic linking (Zero Trust)
            $stmtLast = $db->query("SELECT signature_hash FROM audit_logs ORDER BY id DESC LIMIT 1");
            $lastHash = $stmtLast->fetchColumn() ?: 'genesis_block';

            $payload = json_encode($data['details']);
            $signatureHash = hash('sha256', $lastHash . $data['action'] . $payload . $data['timestamp'] . $data['ip']);

            $tenantId = Config::get('current_tenant_id', 1);

            $sql = "INSERT INTO audit_logs (request_id, tenant_id, user_id, user_email, user_role, action, details, level, ip_address, user_agent, request_uri, request_method, signature_hash, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['request_id'],
                $tenantId,
                $data['user_id'],
                $data['user_email'],
                Auth::user()['role'] ?? 'guest',
                $data['action'],
                $payload,
                $data['severity'],
                $data['ip'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'none',
                $data['route'],
                $data['method'],
                $signatureHash,
                $data['timestamp']
            ]);
        } catch (\Exception $dbEx) {
            error_log("DB Logging failed: " . $dbEx->getMessage());
        }
    }

    private static function logToFile(array $data): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/security_' . date('Y-m-d') . '.json';
        file_put_contents($logFile, json_encode($data) . PHP_EOL, FILE_APPEND);
    }

    private static function logToImmutableStorage(array $data): void
    {
        // En producción real, esto enviaría datos a AWS CloudWatch, Datadog o Splunk
        // Aquí simulamos un 'vault' (bóveda segura) de solo adjuntar.
        $logDir = BASE_PATH . '/storage/audit_vault';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/immutable_vault_' . date('Y-m') . '.log';
        $entry = sprintf(
            "[%s] [%s] %s | IP: %s | User: %s | Data: %s\n",
            $data['timestamp'],
            $data['severity'],
            $data['action'],
            $data['ip'],
            $data['user_email'],
            json_encode($data['details'])
        );

        // Uso de LOCK_EX previene corrupción si múltiples hilos escriben a la vez
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
