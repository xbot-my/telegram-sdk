<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

/**
 * 配置接口
 * 
 * 定义配置管理的标准接口
 */
interface ConfigInterface
{
    /**
     * 获取配置值
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * 设置配置值
     */
    public function set(string $key, mixed $value): static;

    /**
     * 检查配置是否存在
     */
    public function has(string $key): bool;

    /**
     * 删除配置
     */
    public function forget(string $key): static;

    /**
     * 获取所有配置
     */
    public function all(): array;

    /**
     * 合并配置
     */
    public function merge(array $config): static;

    /**
     * 替换配置
     */
    public function replace(array $config): static;

    /**
     * 清空配置
     */
    public function clear(): static;

    /**
     * 获取 Bot 配置
     */
    public function getBot(string $name = null): array;

    /**
     * 设置 Bot 配置
     */
    public function setBot(string $name, array $config): static;

    /**
     * 获取所有 Bot 配置
     */
    public function getAllBots(): array;

    /**
     * 检查 Bot 配置是否存在
     */
    public function hasBot(string $name): bool;

    /**
     * 删除 Bot 配置
     */
    public function removeBot(string $name): static;

    /**
     * 获取默认 Bot 名称
     */
    public function getDefaultBot(): string;

    /**
     * 设置默认 Bot 名称
     */
    public function setDefaultBot(string $name): static;

    /**
     * 获取 HTTP 客户端配置
     */
    public function getHttpClient(): array;

    /**
     * 设置 HTTP 客户端配置
     */
    public function setHttpClient(array $config): static;

    /**
     * 获取缓存配置
     */
    public function getCache(): array;

    /**
     * 设置缓存配置
     */
    public function setCache(array $config): static;

    /**
     * 获取日志配置
     */
    public function getLogging(): array;

    /**
     * 设置日志配置
     */
    public function setLogging(array $config): static;

    /**
     * 获取中间件配置
     */
    public function getMiddleware(): array;

    /**
     * 设置中间件配置
     */
    public function setMiddleware(array $config): static;

    /**
     * 获取 Webhook 配置
     */
    public function getWebhook(): array;

    /**
     * 设置 Webhook 配置
     */
    public function setWebhook(array $config): static;

    /**
     * 获取安全配置
     */
    public function getSecurity(): array;

    /**
     * 设置安全配置
     */
    public function setSecurity(array $config): static;

    /**
     * 获取性能配置
     */
    public function getPerformance(): array;

    /**
     * 设置性能配置
     */
    public function setPerformance(array $config): static;

    /**
     * 获取调试配置
     */
    public function getDebug(): array;

    /**
     * 设置调试配置
     */
    public function setDebug(array $config): static;

    /**
     * 验证配置
     */
    public function validate(): array;

    /**
     * 检查配置是否有效
     */
    public function isValid(): bool;

    /**
     * 获取配置验证错误
     */
    public function getValidationErrors(): array;

    /**
     * 从文件加载配置
     */
    public function loadFromFile(string $path): static;

    /**
     * 保存配置到文件
     */
    public function saveToFile(string $path): bool;

    /**
     * 从数组加载配置
     */
    public function loadFromArray(array $config): static;

    /**
     * 从环境变量加载配置
     */
    public function loadFromEnvironment(): static;

    /**
     * 获取配置快照
     */
    public function snapshot(): array;

    /**
     * 恢复配置快照
     */
    public function restore(array $snapshot): static;

    /**
     * 获取配置变更历史
     */
    public function getHistory(): array;

    /**
     * 清空配置变更历史
     */
    public function clearHistory(): static;

    /**
     * 监听配置变更
     */
    public function onChange(callable $callback): static;

    /**
     * 移除配置变更监听器
     */
    public function removeOnChange(callable $callback): static;

    /**
     * 获取配置源信息
     */
    public function getSource(): array;

    /**
     * 设置配置源信息
     */
    public function setSource(array $source): static;

    /**
     * 获取配置修改时间
     */
    public function getLastModified(): ?\DateTimeInterface;

    /**
     * 检查配置是否已修改
     */
    public function isModified(): bool;

    /**
     * 重新加载配置
     */
    public function reload(): static;

    /**
     * 获取配置统计信息
     */
    public function getStats(): array;

    /**
     * 检查配置系统健康状态
     */
    public function healthCheck(): bool;
}