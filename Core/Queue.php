<?php
namespace Core;

class Queue
{
    /**
     * Push a new job onto the queue.
     * RF-06: Soporte para Redis Broker + DB Persistence
     *
     * @param string $jobClass Fully qualified class name of the job
     * @param array $payload Data to pass to the job's handle method
     * @return int The ID of the created job (DB ID)
     */
    public static function push(string $jobClass, array $payload = [])
    {
        $db = Database::getInstance()->getConnection();
        $tenantId = Config::get('current_tenant_id', 1);

        // 1. Persistencia en Base de Datos (Auditoría/Retry)
        $sql = "INSERT INTO jobs (job_class, payload, tenant_id, status, attempts, created_at) VALUES (?, ?, ?, 'pending', 0, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$jobClass, json_encode($payload), $tenantId]);
        $jobId = $db->lastInsertId();

        // 2. Broker de Mensajería: Redis (Performance Crítica)
        try {
            self::dispatchToRedis([
                'id' => $jobId,
                'class' => $jobClass,
                'payload' => $payload,
                'tenant_id' => $tenantId,
                'pushed_at' => microtime(true)
            ]);
        } catch (\Exception $e) {
            // Si Redis falla, el worker DB fallback tomará el relevo (Resiliencia)
            error_log("[Core\Queue] Redis push failed. Falling back to DB-only: " . $e->getMessage());
        }

        return $jobId;
    }

    /**
     * Envía la señal al broker Redis (LPUSH)
     */
    private static function dispatchToRedis(array $data)
    {
        $host = Config::get('REDIS_HOST', '127.0.0.1');
        $port = (int) Config::get('REDIS_PORT', 6379);

        // Usamos Predis o PHPRedis según disponibilidad
        if (class_exists('\Predis\Client')) {
            $client = new \Predis\Client(['host' => $host, 'port' => $port]);
            $client->lpush('vezetaelea_default_queue', json_encode($data));
        } elseif (extension_loaded('redis')) {
            $redis = new \Redis();
            $redis->connect($host, $port);
            $redis->lPush('vezetaelea_default_queue', json_encode($data));
        } else {
            throw new \Exception("Redis driver not available (Predis or PHPRedis required).");
        }
    }
}

