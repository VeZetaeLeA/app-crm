<?php
/**
 * Data Wyrd OS - Console Queue Worker
 * Run via CLI: php worker.php
 */

define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/env.php';
\EnvLoader::load(__DIR__ . '/.env');

// 3. Autoload Estructural (Composer)
require_once BASE_PATH . '/vendor/autoload.php';

// Fallback para clases no gestionadas por composer
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

function base_path($path = '')
{
    return BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
}

\Core\Config::load();

$db = \Core\Database::getInstance()->getConnection();

// Configuración de Redis
$redisHost = \Core\Config::get('REDIS_HOST', '127.0.0.1');
$redisPort = (int) \Core\Config::get('REDIS_PORT', 6379);
$redisClient = null;

try {
    if (class_exists('\Predis\Client')) {
        $redisClient = new \Predis\Client(['host' => $redisHost, 'port' => $redisPort]);
        echo "Connected to Redis Broker (Predis).\n";
    } elseif (extension_loaded('redis')) {
        $redisClient = new \Redis();
        $redisClient->connect($redisHost, $redisPort);
        echo "Connected to Redis Broker (PHPRedis).\n";
    }
} catch (\Exception $e) {
    echo "Warning: Redis Broker not available. Falling back to DB Polling.\n";
}

echo "Starting VeZetaeLeA High-Performance Queue Worker...\n";

while (true) {
    try {
        $jobData = null;

        // 1. Prioridad: Redis BRPOP (Bloqueo eficiente para tiempo real)
        if ($redisClient) {
            try {
                // brpop devuelve [queue_name, data]
                $result = $redisClient->brpop(['vezetaelea_default_queue'], 2); // Timeout de 2 segundos
                if ($result) {
                    $jobData = is_array($result) ? json_decode($result[1], true) : json_decode($result, true);
                }
            } catch (\Exception $re) {
                echo "Redis error, switching to DB: " . $re->getMessage() . "\n";
            }
        }

        // 2. Fallback: Base de Datos (Para trabajos que Redis perdió o reintentos)
        if (!$jobData) {
            $stmt = $db->query("SELECT * FROM jobs WHERE status = 'pending' AND attempts < 3 ORDER BY created_at ASC LIMIT 1");
            $dbJob = $stmt->fetch();
            if ($dbJob) {
                $jobData = [
                    'id' => $dbJob['id'],
                    'class' => $dbJob['job_class'],
                    'payload' => json_decode($dbJob['payload'], true),
                    'tenant_id' => $dbJob['tenant_id']
                ];
            }
        }

        if ($jobData) {
            $jobId = $jobData['id'];
            $jobClass = $jobData['class'];
            $payload = $jobData['payload'];
            $tenantId = $jobData['tenant_id'] ?? 1;

            echo "[" . date('Y-m-d H:i:s') . "] Processing Job ID {$jobId} ({$jobClass}) for Tenant {$tenantId}\n";

            // Marcar como en proceso
            $db->prepare("UPDATE jobs SET status = 'processing', attempts = attempts + 1 WHERE id = ?")->execute([$jobId]);

            // Inyectar contexto de Tenant
            \Core\Config::set('current_tenant_id', $tenantId);

            if (class_exists($jobClass)) {
                $instance = new $jobClass();
                if ($instance instanceof \Core\Queue\JobInterface) {
                    try {
                        $instance->handle($payload);
                        
                        // Éxito: Eliminar o marcar como completado
                        $db->prepare("UPDATE jobs SET status = 'completed', completed_at = NOW() WHERE id = ?")->execute([$jobId]);
                        // Opcional: ELiminar para ahorrar espacio
                        // $db->prepare("DELETE FROM jobs WHERE id = ?")->execute([$jobId]);
                        
                        echo "  ✔ Job ID {$jobId} completed.\n";
                    } catch (\Exception $jobEx) {
                        handleJobFailure($db, $jobId, $jobEx->getMessage());
                    }
                } else {
                    handleJobFailure($db, $jobId, "Job class must implement JobInterface.");
                }
            } else {
                handleJobFailure($db, $jobId, "Class {$jobClass} not found.");
            }
        } else {
            // Sin trabajos, sleep pequeño si no usamos BRPOP
            if (!$redisClient) sleep(2);
        }

    } catch (\Exception $e) {
        echo "Worker Main Loop Error: " . $e->getMessage() . "\n";
        sleep(5);
    }
}

/**
 * RF-06: Manejo de reintentos y Dead Letter Queue (DLQ)
 */
function handleJobFailure($db, $jobId, $error) {
    echo "  ✘ Job ID {$jobId} failed: {$error}\n";
    
    // Obtener intentos actuales
    $stmt = $db->prepare("SELECT attempts FROM jobs WHERE id = ?");
    $stmt->execute([$jobId]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= 3) {
        // Mover a "Dead Letter" o estado fallido final
        $db->prepare("UPDATE jobs SET status = 'failed', error_message = ?, failed_at = NOW() WHERE id = ?")
           ->execute([$error, $jobId]);
    } else {
        // Marcar para reintento
        $db->prepare("UPDATE jobs SET status = 'pending', error_message = ? WHERE id = ?")
           ->execute([$error, $jobId]);
    }
}

