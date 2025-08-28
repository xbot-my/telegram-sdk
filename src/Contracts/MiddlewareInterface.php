<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 中间件接口
 * 
 * 定义所有 HTTP 中间件的标准接口
 */
interface MiddlewareInterface
{
    /**
     * 处理请求
     * 
     * @param RequestInterface $request 请求对象
     * @param callable $next 下一个中间件或处理器
     * @return ResponseInterface 响应对象
     */
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface;

    /**
     * 获取中间件名称
     */
    public function getName(): string;

    /**
     * 获取中间件优先级
     * 优先级越高，执行顺序越靠前
     */
    public function getPriority(): int;

    /**
     * 检查中间件是否启用
     */
    public function isEnabled(): bool;

    /**
     * 启用中间件
     */
    public function enable(): static;

    /**
     * 禁用中间件
     */
    public function disable(): static;

    /**
     * 获取中间件配置
     */
    public function getConfig(): array;

    /**
     * 设置中间件配置
     */
    public function setConfig(array $config): static;

    /**
     * 获取中间件统计信息
     */
    public function getStats(): array;

    /**
     * 重置中间件统计信息
     */
    public function resetStats(): void;

    /**
     * 检查中间件健康状态
     */
    public function healthCheck(): bool;
}