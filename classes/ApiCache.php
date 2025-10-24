<?php namespace Aero\Clouds\Classes;

use Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * ApiCache Class
 *
 * Sistema de cache inteligente con Redis para optimizar consultas grandes.
 * Invalida automáticamente cuando los datos cambian.
 *
 * Uso:
 * ```php
 * $data = ApiCache::remember('orders.list.user.123', function() {
 *     return Order::with('invoice')->where('user_id', 123)->get();
 * }, 3600);
 * ```
 */
class ApiCache
{
    /**
     * Prefijo para todas las claves de cache de la API
     */
    const PREFIX = 'clouds_api:';

    /**
     * TTL por defecto (1 hora)
     */
    const DEFAULT_TTL = 3600;

    /**
     * TTL para consultas grandes (6 horas)
     */
    const LARGE_QUERY_TTL = 21600;

    /**
     * TTL para schemas (24 horas - casi nunca cambian)
     */
    const SCHEMA_TTL = 86400;

    /**
     * Recordar datos en cache con callback
     *
     * @param string $key Clave de cache
     * @param callable $callback Función que retorna los datos
     * @param int|null $ttl Tiempo de vida en segundos
     * @param array $tags Tags para invalidación grupal
     * @return mixed
     */
    public static function remember($key, callable $callback, $ttl = null, array $tags = [])
    {
        $ttl = $ttl ?? self::DEFAULT_TTL;
        $fullKey = self::PREFIX . $key;

        try {
            // Intentar obtener de Redis primero (más rápido)
            if (self::isRedisAvailable()) {
                $cached = Redis::get($fullKey);

                if ($cached !== null) {
                    Log::debug("[ApiCache] HIT from Redis: {$key}");
                    return unserialize($cached);
                }
            }

            // Si no está en Redis, ejecutar callback
            Log::debug("[ApiCache] MISS: {$key} - Executing callback");
            $data = $callback();

            // Guardar en Redis
            if (self::isRedisAvailable()) {
                Redis::setex($fullKey, $ttl, serialize($data));

                // Guardar tags para invalidación grupal
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        $tagKey = self::PREFIX . 'tag:' . $tag;
                        Redis::sadd($tagKey, $fullKey);
                        Redis::expire($tagKey, $ttl);
                    }
                }
            }

            return $data;

        } catch (\Exception $e) {
            Log::error("[ApiCache] Error: {$e->getMessage()} - Returning raw data");
            return $callback();
        }
    }

    /**
     * Obtener datos de cache (sin callback)
     *
     * @param string $key Clave de cache
     * @return mixed|null
     */
    public static function get($key)
    {
        $fullKey = self::PREFIX . $key;

        try {
            if (self::isRedisAvailable()) {
                $cached = Redis::get($fullKey);
                if ($cached !== null) {
                    return unserialize($cached);
                }
            }
        } catch (\Exception $e) {
            Log::error("[ApiCache] Get error: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Guardar datos en cache
     *
     * @param string $key Clave de cache
     * @param mixed $value Valor a guardar
     * @param int|null $ttl Tiempo de vida
     * @param array $tags Tags para invalidación
     * @return bool
     */
    public static function put($key, $value, $ttl = null, array $tags = [])
    {
        $ttl = $ttl ?? self::DEFAULT_TTL;
        $fullKey = self::PREFIX . $key;

        try {
            if (self::isRedisAvailable()) {
                Redis::setex($fullKey, $ttl, serialize($value));

                // Guardar tags
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        $tagKey = self::PREFIX . 'tag:' . $tag;
                        Redis::sadd($tagKey, $fullKey);
                        Redis::expire($tagKey, $ttl);
                    }
                }

                return true;
            }
        } catch (\Exception $e) {
            Log::error("[ApiCache] Put error: {$e->getMessage()}");
        }

        return false;
    }

    /**
     * Invalidar cache por clave específica
     *
     * @param string $key Clave de cache
     * @return bool
     */
    public static function forget($key)
    {
        $fullKey = self::PREFIX . $key;

        try {
            if (self::isRedisAvailable()) {
                Redis::del($fullKey);
                Log::info("[ApiCache] Invalidated: {$key}");
                return true;
            }
        } catch (\Exception $e) {
            Log::error("[ApiCache] Forget error: {$e->getMessage()}");
        }

        return false;
    }

    /**
     * Invalidar todo el cache de un tag
     *
     * @param string $tag Tag a invalidar
     * @return int Número de claves invalidadas
     */
    public static function forgetTag($tag)
    {
        $tagKey = self::PREFIX . 'tag:' . $tag;
        $count = 0;

        try {
            if (self::isRedisAvailable()) {
                // Obtener todas las claves del tag
                $keys = Redis::smembers($tagKey);

                foreach ($keys as $key) {
                    Redis::del($key);
                    $count++;
                }

                // Eliminar el tag
                Redis::del($tagKey);

                Log::info("[ApiCache] Invalidated tag '{$tag}': {$count} keys");
            }
        } catch (\Exception $e) {
            Log::error("[ApiCache] ForgetTag error: {$e->getMessage()}");
        }

        return $count;
    }

    /**
     * Limpiar todo el cache de la API
     *
     * @return int Número de claves eliminadas
     */
    public static function flush()
    {
        $count = 0;

        try {
            if (self::isRedisAvailable()) {
                // Buscar todas las claves con el prefijo
                $keys = Redis::keys(self::PREFIX . '*');

                foreach ($keys as $key) {
                    Redis::del($key);
                    $count++;
                }

                Log::info("[ApiCache] Flushed entire cache: {$count} keys");
            }
        } catch (\Exception $e) {
            Log::error("[ApiCache] Flush error: {$e->getMessage()}");
        }

        return $count;
    }

    /**
     * Generar clave de cache para una query
     *
     * @param string $model Nombre del modelo
     * @param string $action Acción (list, get, etc.)
     * @param array $params Parámetros de la query
     * @return string
     */
    public static function generateKey($model, $action, array $params = [])
    {
        // Ordenar params para que queries iguales generen la misma clave
        ksort($params);

        $paramsHash = md5(json_encode($params));

        return strtolower($model) . '.' . $action . '.' . $paramsHash;
    }

    /**
     * Invalidar cache de un modelo específico
     *
     * @param string $modelClass Clase del modelo
     * @return int Número de claves invalidadas
     */
    public static function invalidateModel($modelClass)
    {
        $modelName = strtolower(class_basename($modelClass));
        return self::forgetTag($modelName);
    }

    /**
     * Verificar si Redis está disponible
     *
     * @return bool
     */
    protected static function isRedisAvailable()
    {
        static $available = null;

        if ($available === null) {
            try {
                Redis::ping();
                $available = true;
            } catch (\Exception $e) {
                Log::warning("[ApiCache] Redis not available: {$e->getMessage()}");
                $available = false;
            }
        }

        return $available;
    }

    /**
     * Obtener estadísticas del cache
     *
     * @return array
     */
    public static function stats()
    {
        $stats = [
            'redis_available' => false,
            'total_keys' => 0,
            'memory_used' => 0,
            'tags' => []
        ];

        try {
            if (self::isRedisAvailable()) {
                $stats['redis_available'] = true;

                // Contar claves
                $keys = Redis::keys(self::PREFIX . '*');
                $stats['total_keys'] = count($keys);

                // Contar tags
                $tagKeys = Redis::keys(self::PREFIX . 'tag:*');
                foreach ($tagKeys as $tagKey) {
                    $tag = str_replace(self::PREFIX . 'tag:', '', $tagKey);
                    $stats['tags'][$tag] = Redis::scard($tagKey);
                }

                // Memoria usada (aproximada)
                $stats['memory_used'] = Redis::info('memory')['used_memory_human'] ?? 'N/A';
            }
        } catch (\Exception $e) {
            Log::error("[ApiCache] Stats error: {$e->getMessage()}");
        }

        return $stats;
    }

    /**
     * Determinar si una consulta debe ser cacheada
     *
     * @param string $action Acción
     * @param int $resultCount Número de resultados
     * @return bool
     */
    public static function shouldCache($action, $resultCount = 0)
    {
        // Solo cachear lecturas
        if (!in_array($action, ['list', 'get', 'schema'])) {
            return false;
        }

        // Schemas siempre se cachean (casi nunca cambian)
        if ($action === 'schema') {
            return true;
        }

        // Consultas grandes (>10 registros) se benefician más del cache
        if ($resultCount > 10) {
            return true;
        }

        // Por defecto, cachear consultas de lectura
        return true;
    }

    /**
     * Obtener TTL apropiado según el tipo de consulta
     *
     * @param string $action Acción
     * @param int $resultCount Número de resultados
     * @return int
     */
    public static function getTTL($action, $resultCount = 0)
    {
        // Schemas casi nunca cambian
        if ($action === 'schema') {
            return self::SCHEMA_TTL;
        }

        // Consultas grandes (más costosas) se cachean más tiempo
        if ($resultCount > 50) {
            return self::LARGE_QUERY_TTL;
        }

        return self::DEFAULT_TTL;
    }
}
