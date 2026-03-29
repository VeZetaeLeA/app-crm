<?php
namespace Core;

/**
 * Cache Wrapper with Redis Support
 */
class Cache
{
    private static $redis = null;

    private static function init()
    {
        if (self::$redis === null) {
            // Se puede configurar vía .env si se quiere forzar Redis (ej: CACHE_DRIVER=redis)
            $redisHost = Config::get('REDIS_HOST', '127.0.0.1');
            $redisPort = (int) Config::get('REDIS_PORT', 6379);

            if (extension_loaded('redis')) {
                try {
                    self::$redis = new \Redis();
                    self::$redis->connect($redisHost, $redisPort);
                } catch (\Exception $e) {
                    self::$redis = false; // Silent fail to fallback
                }
            } else {
                self::$redis = false;
            }
        }
    }

    /**
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param callable $callback Function that returns data to cache
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        self::init();

        // 1. Redis Cache
        if (self::$redis) {
            $cached = self::$redis->get($key);
            if ($cached !== false) {
                return unserialize($cached);
            }

            $data = $callback();
            self::$redis->setex($key, $ttl, serialize($data));
            return $data;
        }

        // 2. Simple File Cache Fallback if Redis is not available
        $cacheDir = BASE_PATH . '/storage/cache/';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . md5($key) . '.cache';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            return unserialize(file_get_contents($cacheFile));
        }

        $data = $callback();
        file_put_contents($cacheFile, serialize($data));
        
        return $data;
    }

    public static function forget(string $key)
    {
        self::init();
        if (self::$redis) {
            self::$redis->del($key);
        } else {
            $cacheFile = BASE_PATH . '/storage/cache/' . md5($key) . '.cache';
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }
}
