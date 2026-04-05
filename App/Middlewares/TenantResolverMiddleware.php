<?php
namespace App\Middlewares;

use Core\Middleware;
use Core\Config;
use Core\Database;
use PDO;

/**
 * Tenant Resolver Middleware
 * Identifies the tenant for the current request and scope data accordingly.
 */
class TenantResolverMiddleware implements Middleware
{
    public function handle($params = [])
    {
        // 1. Resolve host (e.g., tenant-a.Vezetaelea.com)
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // 2. Lookup tenant in DB (cached implementation would be better)
        $tenantId = null;
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM tenants WHERE domain = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$host]);
            $tenantId = $stmt->fetchColumn();
        } catch (\PDOException $e) {
            // Silently fail if table does not exist
        }

        // 3. Set global context (for simplicity, we'll use Config)
        if ($tenantId) {
            Config::set('current_tenant_id', (int) $tenantId);
        } else {
            // Default to first tenant or error
            Config::set('current_tenant_id', 1);
        }
    }
}
