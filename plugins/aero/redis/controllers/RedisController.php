<?php

namespace Aero\Redis\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Exception;

/**
 * Redis Controller - API para monitor Redis
 */
class RedisController extends Controller
{
    /**
     * Obtener estadísticas de Redis
     */
    public function getStats()
    {
        try {
            $redis = Redis::connection();
            
            // Obtener información del servidor
            $info = $redis->info();
            
            // Procesar información
            $stats = [
                // Server info
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'uptime_in_seconds' => $info['uptime_in_seconds'] ?? 0,
                'tcp_port' => $info['tcp_port'] ?? 6379,
                
                // Memory info
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'used_memory_peak' => $info['used_memory_peak'] ?? 0,
                'maxmemory' => $info['maxmemory'] ?? 0,
                
                // Stats
                'total_connections_received' => $info['total_connections_received'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'instantaneous_ops_per_sec' => $info['instantaneous_ops_per_sec'] ?? 0,
                'connected_clients' => $info['connected_clients'] ?? 0,
                
                // Keyspace
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'expired_keys' => $info['expired_keys'] ?? 0,
                'evicted_keys' => $info['evicted_keys'] ?? 0,
            ];
            
            // Obtener información de databases
            $databases = $this->getDatabasesInfo();
            
            // Calcular total de keys
            $totalKeys = array_sum(array_column($databases, 'keys'));
            $stats['total_keys'] = $totalKeys;
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'databases' => $databases,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error connecting to Redis'
            ], 500);
        }
    }

    /**
     * Limpiar base de datos específica
     */
    public function flushDatabase(Request $request, $db)
    {
        try {
            $db = (int) $db;
            
            // Validar database number
            if ($db < 0 || $db > 15) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid database number. Must be between 0 and 15.'
                ], 400);
            }
            
            // Conectar a la database específica
            $redis = Redis::connection();
            $redis->select($db);
            
            // Limpiar database
            $result = $redis->flushdb();
            
            return response()->json([
                'success' => true,
                'message' => "Database {$db} flushed successfully",
                'database' => $db,
                'result' => $result
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => "Error flushing database {$db}"
            ], 500);
        }
    }

    /**
     * Obtener keys de una database específica
     */
    public function getKeys(Request $request, $db)
    {
        try {
            $db = (int) $db;
            $pattern = $request->get('pattern', '*');
            $limit = $request->get('limit', 100);
            
            $redis = Redis::connection();
            $redis->select($db);
            
            // Obtener keys con pattern
            $keys = $redis->keys($pattern);
            
            // Limitar resultados
            $keys = array_slice($keys, 0, $limit);
            
            // Obtener información adicional de cada key
            $keysInfo = [];
            foreach ($keys as $key) {
                $type = $redis->type($key);
                $ttl = $redis->ttl($key);
                
                $keysInfo[] = [
                    'key' => $key,
                    'type' => $type,
                    'ttl' => $ttl,
                    'size' => $this->getKeySize($redis, $key, $type)
                ];
            }
            
            return response()->json([
                'success' => true,
                'database' => $db,
                'pattern' => $pattern,
                'total_found' => count($keys),
                'keys' => $keysInfo
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => "Error getting keys from database {$db}"
            ], 500);
        }
    }

    /**
     * Obtener información de todas las databases
     */
    private function getDatabasesInfo()
    {
        $databases = [];
        $redis = Redis::connection();
        
        // Revisar databases 0-15 (máximo estándar de Redis)
        for ($db = 0; $db <= 15; $db++) {
            try {
                $redis->select($db);
                $dbInfo = $redis->info('keyspace');
                
                // Parsear información de la database
                $dbKey = "db{$db}";
                if (isset($dbInfo[$dbKey])) {
                    $info = $dbInfo[$dbKey];
                    // Format: keys=X,expires=Y,avg_ttl=Z
                    preg_match('/keys=(\d+),expires=(\d+)/', $info, $matches);
                    
                    $databases[$db] = [
                        'keys' => (int) ($matches[1] ?? 0),
                        'expires' => (int) ($matches[2] ?? 0),
                        'info' => $info
                    ];
                } else {
                    // Database vacía
                    $databases[$db] = [
                        'keys' => 0,
                        'expires' => 0,
                        'info' => null
                    ];
                }
            } catch (Exception $e) {
                // Database no accesible o error
                continue;
            }
        }
        
        return $databases;
    }

    /**
     * Obtener tamaño aproximado de una key
     */
    private function getKeySize($redis, $key, $type)
    {
        try {
            switch ($type) {
                case 'string':
                    return strlen($redis->get($key));
                    
                case 'list':
                    return $redis->llen($key);
                    
                case 'set':
                    return $redis->scard($key);
                    
                case 'zset':
                    return $redis->zcard($key);
                    
                case 'hash':
                    return $redis->hlen($key);
                    
                default:
                    return 0;
            }
        } catch (Exception $e) {
            return 0;
        }
    }
}