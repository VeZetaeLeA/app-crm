<?php
namespace App\Repositories;

use Core\Database;
use PDO;

/**
 * Abstract Base Repository
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected $db;
    protected string $table;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Ejecuta una consulta preparada.
     */
    protected function execute(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Obtiene un solo registro.
     */
    protected function fetch(string $sql, array $params = [], int $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $this->execute($sql, $params)->fetch($fetchMode);
    }

    /**
     * Obtiene todos los registros.
     */
    protected function fetchAll(string $sql, array $params = [], int $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $this->execute($sql, $params)->fetchAll($fetchMode);
    }

    /**
     * Obtiene el ID del tenant actual validando que sea obligatorio en SaaS
     */
    protected function getTenantId(): int
    {
        $tenantId = \Core\Config::get('current_tenant_id');
        
        if (!$tenantId && \Core\Config::get('ENVIRONMENT') === 'production') {
            \Core\SecurityLogger::log('CRITICAL_REPOS_ERROR', "Acceso a repositorio {$this->table} sin contexto de Tenant activo.", 'CRITICAL');
            throw new \Exception("Illegal repository access: No active tenant context.");
        }

        return (int) ($tenantId ?: 1); // Fallback a 1 solo en dev/local
    }

    public function all()
    {
        $tenantId = $this->getTenantId();
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll() ?: [];
    }

    public function find(int $id)
    {
        $tenantId = $this->getTenantId();
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND tenant_id = ? AND deleted_at IS NULL LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $tenantId]);
        $row = $stmt->fetch();
        
        return $row ?: null;
    }

    public function create(array $data)
    {
        // Garantía SaaS: Auto-inyectar context de tenant
        $data['tenant_id'] = $this->getTenantId();
        
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data)
    {
        $tenantId = $this->getTenantId();
        
        // Evitar manipulación de tenant_id en updates
        unset($data['tenant_id']);
        
        $fields = "";
        foreach ($data as $key => $value) {
            $fields .= "{$key} = ?, ";
        }
        $fields = rtrim($fields, ', ');

        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = ? AND tenant_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $values = array_values($data);
        $values[] = $id;
        $values[] = $tenantId;
        
        return $stmt->execute($values);
    }

    public function delete(int $id)
    {
        $tenantId = $this->getTenantId();
        // Implementamos Soft Delete por defecto para seguridad SaaS
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ? AND tenant_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $tenantId]);
    }
}

