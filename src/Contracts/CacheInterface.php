<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

/**
 * 缓存接口
 * 
 * 定义缓存操作的标准接口
 */
interface CacheInterface
{
    /**
     * 获取缓存值
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * 设置缓存值
     */
    public function set(string $key, mixed $value, int|\DateInterval $ttl = null): bool;

    /**
     * 删除缓存
     */
    public function delete(string $key): bool;

    /**
     * 清空所有缓存
     */
    public function clear(): bool;

    /**
     * 检查缓存是否存在
     */
    public function has(string $key): bool;

    /**
     * 批量获取缓存值
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * 批量设置缓存值
     */
    public function setMultiple(iterable $values, int|\DateInterval $ttl = null): bool;

    /**
     * 批量删除缓存
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * 原子性递增
     */
    public function increment(string $key, int $value = 1): int|false;

    /**
     * 原子性递减
     */
    public function decrement(string $key, int $value = 1): int|false;

    /**
     * 获取并删除缓存
     */
    public function pull(string $key, mixed $default = null): mixed;

    /**
     * 记住缓存值（如果不存在则执行回调并缓存结果）
     */
    public function remember(string $key, int|\DateInterval $ttl, callable $callback): mixed;

    /**
     * 永久记住缓存值
     */
    public function rememberForever(string $key, callable $callback): mixed;

    /**
     * 忘记缓存值
     */
    public function forget(string $key): bool;

    /**
     * 刷新缓存（删除所有缓存）
     */
    public function flush(): bool;

    /**
     * 获取缓存键的剩余生存时间
     */
    public function ttl(string $key): int;

    /**
     * 设置缓存键的过期时间
     */
    public function expire(string $key, int|\DateInterval $ttl): bool;

    /**
     * 移除缓存键的过期时间
     */
    public function persist(string $key): bool;

    /**
     * 获取所有缓存键
     */
    public function keys(string $pattern = '*'): array;

    /**
     * 获取缓存大小
     */
    public function size(): int;

    /**
     * 获取缓存统计信息
     */
    public function stats(): array;

    /**
     * 获取缓存存储驱动名称
     */
    public function getDriverName(): string;

    /**
     * 获取缓存键前缀
     */
    public function getPrefix(): string;

    /**
     * 设置缓存键前缀
     */
    public function setPrefix(string $prefix): static;

    /**
     * 生成缓存键
     */
    public function key(string ...$segments): string;

    /**
     * 标记缓存（用于缓存标签功能）
     */
    public function tags(string|array $tags): static;

    /**
     * 根据标签刷新缓存
     */
    public function flushTags(string|array $tags): bool;

    /**
     * 锁定缓存键
     */
    public function lock(string $key, int $seconds = null): bool;

    /**
     * 释放缓存锁
     */
    public function unlock(string $key): bool;

    /**
     * 检查缓存键是否被锁定
     */
    public function isLocked(string $key): bool;

    /**
     * 获取或设置带锁的缓存
     */
    public function lockAndGet(string $key, callable $callback, int $lockSeconds = 10, int $ttl = null): mixed;

    /**
     * 序列化值
     */
    public function serialize(mixed $value): string;

    /**
     * 反序列化值
     */
    public function unserialize(string $value): mixed;

    /**
     * 检查缓存连接是否健康
     */
    public function healthCheck(): bool;

    /**
     * 重连缓存存储
     */
    public function reconnect(): bool;

    /**
     * 获取缓存配置
     */
    public function getConfig(): array;
}