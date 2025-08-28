<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * 日志接口
 * 
 * 定义日志记录的标准接口，扩展 PSR-3 LoggerInterface
 */
interface LoggerInterface extends PsrLoggerInterface
{
    /**
     * 记录 API 请求日志
     */
    public function apiRequest(string $method, array $parameters = [], array $context = []): void;

    /**
     * 记录 API 响应日志
     */
    public function apiResponse(string $method, array $response, float $duration = 0.0, array $context = []): void;

    /**
     * 记录 API 错误日志
     */
    public function apiError(string $method, \Throwable $exception, array $context = []): void;

    /**
     * 记录中间件日志
     */
    public function middleware(string $middleware, string $action, array $context = []): void;

    /**
     * 记录缓存操作日志
     */
    public function cache(string $operation, string $key, array $context = []): void;

    /**
     * 记录文件操作日志
     */
    public function file(string $operation, string $path, array $context = []): void;

    /**
     * 记录验证错误日志
     */
    public function validation(array $errors, array $context = []): void;

    /**
     * 记录性能指标日志
     */
    public function performance(string $operation, float $duration, array $metrics = []): void;

    /**
     * 记录安全相关日志
     */
    public function security(string $event, array $context = []): void;

    /**
     * 记录 Bot 状态变化日志
     */
    public function botStatus(string $botName, string $status, array $context = []): void;

    /**
     * 记录 Webhook 日志
     */
    public function webhook(string $event, array $data = [], array $context = []): void;

    /**
     * 记录数据库操作日志
     */
    public function database(string $operation, string $query = '', array $bindings = [], float $duration = 0.0): void;

    /**
     * 记录队列任务日志
     */
    public function queue(string $job, string $status, array $context = []): void;

    /**
     * 获取日志上下文
     */
    public function getContext(): array;

    /**
     * 设置日志上下文
     */
    public function setContext(array $context): static;

    /**
     * 添加日志上下文
     */
    public function addContext(string $key, mixed $value): static;

    /**
     * 移除日志上下文
     */
    public function removeContext(string $key): static;

    /**
     * 清空日志上下文
     */
    public function clearContext(): static;

    /**
     * 获取日志级别
     */
    public function getLevel(): string;

    /**
     * 设置日志级别
     */
    public function setLevel(string $level): static;

    /**
     * 检查日志级别是否启用
     */
    public function isLevelEnabled(string $level): bool;

    /**
     * 获取日志处理器
     */
    public function getHandlers(): array;

    /**
     * 添加日志处理器
     */
    public function addHandler(object $handler): static;

    /**
     * 移除日志处理器
     */
    public function removeHandler(object $handler): static;

    /**
     * 获取日志格式化器
     */
    public function getFormatter(): ?object;

    /**
     * 设置日志格式化器
     */
    public function setFormatter(object $formatter): static;

    /**
     * 获取日志统计信息
     */
    public function getStats(): array;

    /**
     * 重置日志统计信息
     */
    public function resetStats(): void;

    /**
     * 获取最近的日志条目
     */
    public function getRecentLogs(int $limit = 100, string $level = null): array;

    /**
     * 清理旧日志文件
     */
    public function cleanup(int $days = 30): int;

    /**
     * 轮转日志文件
     */
    public function rotate(): bool;

    /**
     * 获取日志文件大小
     */
    public function getLogSize(): int;

    /**
     * 获取日志文件路径
     */
    public function getLogPath(): ?string;

    /**
     * 检查日志系统健康状态
     */
    public function healthCheck(): bool;

    /**
     * 刷新日志缓冲区
     */
    public function flush(): void;

    /**
     * 关闭日志记录器
     */
    public function close(): void;
}