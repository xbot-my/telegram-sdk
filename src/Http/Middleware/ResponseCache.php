<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 响应缓存中间件
 * 
 * 缓存HTTP响应以减少重复请求
 */
class ResponseCache
{
    /**
     * 缓存实现
     */
    private ?CacheInterface $cache;

    /**
     * 默认缓存时间（秒）
     */
    private int $defaultTtl;

    /**
     * 是否启用缓存
     */
    private bool $enabled;

    /**
     * 缓存键前缀
     */
    private string $keyPrefix;

    /**
     * 可缓存的HTTP方法
     */
    private array $cacheableMethods;

    /**
     * 可缓存的状态码
     */
    private array $cacheableStatusCodes;

    /**
     * 不可缓存的URI模式
     */
    private array $noCacheUriPatterns;

    /**
     * 缓存策略配置
     */
    private array $cacheStrategies;

    /**
     * 是否压缩缓存内容
     */
    private bool $compressCache;

    /**
     * 最大缓存大小（字节）
     */
    private int $maxCacheSize;

    /**
     * 缓存统计信息
     */
    private array $stats;

    public function __construct(
        ?CacheInterface $cache = null,
        int $defaultTtl = 300,
        bool $enabled = true,
        string $keyPrefix = 'telegram_api:',
        array $cacheableMethods = ['GET'],
        array $cacheableStatusCodes = [200],
        array $noCacheUriPatterns = ['/sendMessage', '/sendPhoto'],
        bool $compressCache = true,
        int $maxCacheSize = 1048576 // 1MB
    ) {
        $this->cache = $cache;
        $this->defaultTtl = max(0, $defaultTtl);
        $this->enabled = $enabled && $cache !== null;
        $this->keyPrefix = $keyPrefix;
        $this->cacheableMethods = $cacheableMethods;
        $this->cacheableStatusCodes = $cacheableStatusCodes;
        $this->noCacheUriPatterns = $noCacheUriPatterns;
        $this->compressCache = $compressCache && extension_loaded('zlib');
        $this->maxCacheSize = max(1024, $maxCacheSize);
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'writes' => 0,
            'errors' => 0,
        ];

        // 初始化缓存策略
        $this->cacheStrategies = [
            'getMe' => 3600,           // 1小时
            'getChat' => 1800,         // 30分钟
            'getChatMember' => 600,    // 10分钟
            'getFile' => 7200,         // 2小时
            'getStickerSet' => 1800,   // 30分钟
            'getMyCommands' => 1800,   // 30分钟
        ];
    }

    /**
     * 尝试从缓存获取响应
     */
    public function get(RequestInterface $request): ?ResponseInterface
    {
        if (!$this->enabled || !$this->isCacheable($request)) {
            return null;
        }

        try {
            $key = $this->generateCacheKey($request);
            $cached = $this->cache->get($key);
            
            if ($cached !== null) {
                $response = $this->unserializeResponse($cached);
                if ($response !== null) {
                    $this->stats['hits']++;
                    return $response;
                }
            }
        } catch (InvalidArgumentException $e) {
            $this->stats['errors']++;
            // 记录错误但继续执行
        }

        $this->stats['misses']++;
        return null;
    }

    /**
     * 将响应存储到缓存
     */
    public function put(
        RequestInterface $request,
        ResponseInterface $response,
        ?int $ttl = null
    ): bool {
        if (!$this->enabled || !$this->isCacheable($request, $response)) {
            return false;
        }

        try {
            $key = $this->generateCacheKey($request);
            $ttl = $ttl ?? $this->getTtlForRequest($request);
            $serialized = $this->serializeResponse($response);
            
            if ($serialized !== null && strlen($serialized) <= $this->maxCacheSize) {
                $success = $this->cache->set($key, $serialized, $ttl);
                if ($success) {
                    $this->stats['writes']++;
                }
                return $success;
            }
        } catch (InvalidArgumentException $e) {
            $this->stats['errors']++;
        }

        return false;
    }

    /**
     * 删除缓存项
     */
    public function delete(RequestInterface $request): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $key = $this->generateCacheKey($request);
            return $this->cache->delete($key);
        } catch (InvalidArgumentException $e) {
            $this->stats['errors']++;
            return false;
        }
    }

    /**
     * 清空所有缓存
     */
    public function clear(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        return $this->cache->clear();
    }

    /**
     * 检查请求是否可缓存
     */
    public function isCacheable(
        RequestInterface $request,
        ?ResponseInterface $response = null
    ): bool {
        // 检查HTTP方法
        if (!in_array($request->getMethod(), $this->cacheableMethods)) {
            return false;
        }

        // 检查URI模式
        $uri = (string) $request->getUri();
        foreach ($this->noCacheUriPatterns as $pattern) {
            if (str_contains($uri, $pattern)) {
                return false;
            }
        }

        // 如果有响应，检查状态码
        if ($response !== null) {
            if (!in_array($response->getStatusCode(), $this->cacheableStatusCodes)) {
                return false;
            }

            // 检查响应头
            if ($response->hasHeader('Cache-Control')) {
                $cacheControl = $response->getHeaderLine('Cache-Control');
                if (str_contains($cacheControl, 'no-cache') || 
                    str_contains($cacheControl, 'no-store') ||
                    str_contains($cacheControl, 'private')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 生成缓存键
     */
    private function generateCacheKey(RequestInterface $request): string
    {
        $uri = (string) $request->getUri();
        $method = $request->getMethod();
        $body = (string) $request->getBody();
        
        // 从URI中提取方法名
        $apiMethod = $this->extractApiMethod($uri);
        
        // 创建唯一键
        $keyData = [
            'method' => $method,
            'uri' => $uri,
            'body' => $body,
            'api_method' => $apiMethod,
        ];
        
        $key = $this->keyPrefix . md5(serialize($keyData));
        
        // 添加方法名以便于调试
        if ($apiMethod) {
            $key .= ':' . $apiMethod;
        }
        
        return $key;
    }

    /**
     * 从URI中提取API方法名
     */
    private function extractApiMethod(string $uri): ?string
    {
        if (preg_match('/\/([a-zA-Z][a-zA-Z0-9_]*)(?:\?|$)/', $uri, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * 获取请求的TTL
     */
    private function getTtlForRequest(RequestInterface $request): int
    {
        $apiMethod = $this->extractApiMethod((string) $request->getUri());
        
        if ($apiMethod && isset($this->cacheStrategies[$apiMethod])) {
            return $this->cacheStrategies[$apiMethod];
        }
        
        return $this->defaultTtl;
    }

    /**
     * 序列化响应
     */
    private function serializeResponse(ResponseInterface $response): ?string
    {
        try {
            $data = [
                'status_code' => $response->getStatusCode(),
                'reason_phrase' => $response->getReasonPhrase(),
                'headers' => $response->getHeaders(),
                'body' => (string) $response->getBody(),
                'protocol_version' => $response->getProtocolVersion(),
                'timestamp' => time(),
            ];
            
            $serialized = serialize($data);
            
            if ($this->compressCache) {
                $compressed = gzcompress($serialized, 6);
                if ($compressed !== false && strlen($compressed) < strlen($serialized)) {
                    return base64_encode('compressed:' . $compressed);
                }
            }
            
            return base64_encode($serialized);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 反序列化响应
     */
    private function unserializeResponse(string $cached): ?ResponseInterface
    {
        try {
            $decoded = base64_decode($cached);
            
            if (str_starts_with($decoded, 'compressed:')) {
                $compressed = substr($decoded, 11);
                $decoded = gzuncompress($compressed);
                if ($decoded === false) {
                    return null;
                }
            }
            
            $data = unserialize($decoded);
            if (!is_array($data)) {
                return null;
            }
            
            // 创建响应对象（这里需要根据实际的HTTP客户端实现调整）
            if (class_exists('\GuzzleHttp\Psr7\Response')) {
                return new \GuzzleHttp\Psr7\Response(
                    $data['status_code'],
                    $data['headers'],
                    $data['body'],
                    $data['protocol_version'],
                    $data['reason_phrase']
                );
            }
            
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 设置缓存策略
     */
    public function setCacheStrategy(string $apiMethod, int $ttl): self
    {
        $this->cacheStrategies[$apiMethod] = max(0, $ttl);
        return $this;
    }

    /**
     * 移除缓存策略
     */
    public function removeCacheStrategy(string $apiMethod): self
    {
        unset($this->cacheStrategies[$apiMethod]);
        return $this;
    }

    /**
     * 添加不可缓存的URI模式
     */
    public function addNoCachePattern(string $pattern): self
    {
        if (!in_array($pattern, $this->noCacheUriPatterns)) {
            $this->noCacheUriPatterns[] = $pattern;
        }
        return $this;
    }

    /**
     * 移除不可缓存的URI模式
     */
    public function removeNoCachePattern(string $pattern): self
    {
        $this->noCacheUriPatterns = array_filter(
            $this->noCacheUriPatterns,
            fn($p) => $p !== $pattern
        );
        return $this;
    }

    /**
     * 启用或禁用缓存
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled && $this->cache !== null;
        return $this;
    }

    /**
     * 设置默认TTL
     */
    public function setDefaultTtl(int $ttl): self
    {
        $this->defaultTtl = max(0, $ttl);
        return $this;
    }

    /**
     * 设置缓存实现
     */
    public function setCache(?CacheInterface $cache): self
    {
        $this->cache = $cache;
        $this->enabled = $this->enabled && $cache !== null;
        return $this;
    }

    /**
     * 获取缓存统计信息
     */
    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;
        
        return array_merge($this->stats, [
            'total_requests' => $total,
            'hit_rate' => round($hitRate, 2),
        ]);
    }

    /**
     * 重置统计信息
     */
    public function resetStats(): self
    {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'writes' => 0,
            'errors' => 0,
        ];
        return $this;
    }

    /**
     * 获取配置信息
     */
    public function getConfig(): array
    {
        return [
            'enabled' => $this->enabled,
            'default_ttl' => $this->defaultTtl,
            'key_prefix' => $this->keyPrefix,
            'cacheable_methods' => $this->cacheableMethods,
            'cacheable_status_codes' => $this->cacheableStatusCodes,
            'no_cache_patterns' => $this->noCacheUriPatterns,
            'cache_strategies' => $this->cacheStrategies,
            'compress_cache' => $this->compressCache,
            'max_cache_size' => $this->maxCacheSize,
            'has_cache_implementation' => $this->cache !== null,
        ];
    }
}