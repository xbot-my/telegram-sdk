<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

use Throwable;

/**
 * 异常处理接口
 * 
 * 定义异常处理的标准接口
 */
interface ExceptionHandlerInterface
{
    /**
     * 处理异常
     */
    public function handle(Throwable $exception, array $context = []): void;

    /**
     * 报告异常
     */
    public function report(Throwable $exception, array $context = []): void;

    /**
     * 渲染异常响应
     */
    public function render(Throwable $exception, array $context = []): array;

    /**
     * 检查异常是否应该被报告
     */
    public function shouldReport(Throwable $exception): bool;

    /**
     * 检查异常是否应该被渲染
     */
    public function shouldRender(Throwable $exception): bool;

    /**
     * 获取异常的严重级别
     */
    public function getSeverity(Throwable $exception): string;

    /**
     * 转换异常为标准格式
     */
    public function transform(Throwable $exception): array;

    /**
     * 获取异常的上下文信息
     */
    public function getContext(Throwable $exception): array;

    /**
     * 设置异常上下文
     */
    public function setContext(array $context): static;

    /**
     * 添加异常监听器
     */
    public function listen(string $exceptionClass, callable $listener): static;

    /**
     * 移除异常监听器
     */
    public function removeListener(string $exceptionClass): static;

    /**
     * 获取异常监听器
     */
    public function getListeners(): array;

    /**
     * 设置异常忽略规则
     */
    public function ignore(string|array $exceptionClasses): static;

    /**
     * 检查异常是否被忽略
     */
    public function isIgnored(Throwable $exception): bool;

    /**
     * 获取忽略的异常类
     */
    public function getIgnored(): array;

    /**
     * 记录异常日志
     */
    public function log(Throwable $exception, string $level = 'error', array $context = []): void;

    /**
     * 发送异常通知
     */
    public function notify(Throwable $exception, array $context = []): void;

    /**
     * 获取异常统计信息
     */
    public function getStats(): array;

    /**
     * 重置异常统计信息
     */
    public function resetStats(): void;

    /**
     * 获取最近的异常
     */
    public function getRecentExceptions(int $limit = 10): array;

    /**
     * 清理异常历史记录
     */
    public function clearHistory(): void;

    /**
     * 设置错误处理器
     */
    public function setErrorHandler(): static;

    /**
     * 恢复默认错误处理器
     */
    public function restoreErrorHandler(): static;

    /**
     * 设置异常处理器
     */
    public function setExceptionHandler(): static;

    /**
     * 恢复默认异常处理器
     */
    public function restoreExceptionHandler(): static;

    /**
     * 设置致命错误处理器
     */
    public function setFatalErrorHandler(): static;

    /**
     * 检查处理器健康状态
     */
    public function healthCheck(): bool;
}