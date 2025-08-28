<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

use XBot\Telegram\TelegramBot;

/**
 * Bot 管理器接口
 * 
 * 定义多 Bot 实例管理的标准接口
 */
interface BotManagerInterface
{
    /**
     * 获取指定名称的 Bot 实例
     */
    public function bot(string $name = null): TelegramBot;

    /**
     * 创建新的 Bot 实例
     */
    public function createBot(string $name, array $config): TelegramBot;

    /**
     * 检查 Bot 实例是否存在
     */
    public function hasBot(string $name): bool;

    /**
     * 移除 Bot 实例
     */
    public function removeBot(string $name): void;

    /**
     * 获取所有 Bot 实例
     */
    public function getAllBots(): array;

    /**
     * 获取默认 Bot 实例
     */
    public function getDefaultBot(): TelegramBot;

    /**
     * 设置默认 Bot 名称
     */
    public function setDefaultBot(string $name): void;

    /**
     * 获取默认 Bot 名称
     */
    public function getDefaultBotName(): string;

    /**
     * 获取所有 Bot 名称
     */
    public function getBotNames(): array;

    /**
     * 获取 Bot 数量
     */
    public function getBotCount(): int;

    /**
     * 清空所有 Bot 实例
     */
    public function clear(): void;

    /**
     * 重新加载指定 Bot 实例
     */
    public function reloadBot(string $name): TelegramBot;

    /**
     * 重新加载所有 Bot 实例
     */
    public function reloadAllBots(): void;

    /**
     * 获取管理器统计信息
     */
    public function getStats(): array;

    /**
     * 检查所有 Bot 的健康状态
     */
    public function healthCheck(): array;
}