<?php

namespace App\Gateways;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CacheGateway
{
    public function __construct() {}

    /**
     * Remember a value in cache with TTL
     */
    public function remember(
        string $key,
        int $ttl,
        callable $callback
    ): mixed {
        return Cache::remember($key, $ttl, function () use ($callback, $key) {
            Log::info('Cache miss', ['cache_key' => $key]);

            return $callback();
        });
    }

    /**
     * Forget a specific cache key
     */
    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Clear cache entries by prefix pattern
     */
    public function clearByPrefix(
        string $prefix
    ): void {
        $store = Cache::getStore();
        if (method_exists($store, 'getRedis')) {
            $this->clearRedisByPattern($prefix.'*');
        } elseif ($store instanceof \Illuminate\Cache\DatabaseStore) {
            $this->clearDatabaseCacheByPrefix($prefix);
        } else {
            Log::warning('Using cache flush fallback for unsupported driver', [
                'driver' => get_class($store),
            ]);
            Cache::flush();
        }

    }

    /**
     * Flush all cache
     */
    public function flush(): bool
    {
        $result = Cache::flush();

        if ($result) {
            Log::info('All cache flushed');
        }

        return $result;
    }

    /**
     * Clear Redis cache entries by pattern
     */
    private function clearRedisByPattern(string $pattern): void
    {
        $store = Cache::getStore();
        if (method_exists($store, 'getRedis')) {

            $redis = $store->getRedis();
            $keys = $redis->keys($pattern);
            if (! empty($keys)) {
                $redis->del($keys);
            }
            Log::info('Redis cache pattern cleared', [
                'pattern' => $pattern,
                'keys_deleted' => count($keys),
            ]);
        }
    }

    /**
     * Clear database cache entries by prefix
     */
    private function clearDatabaseCacheByPrefix(string $prefix): void
    {
        if (config('cache.default') != 'database') {
            throw new InvalidArgumentException('Cache driver not valid');
        }
        DB::table('cache')
            ->where('key', 'like', $prefix.'%')
            ->delete();
    }
}
