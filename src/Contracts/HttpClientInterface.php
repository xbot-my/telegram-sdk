<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * HTTP 客户端接口
 * 
 * 定义 Telegram Bot API HTTP 客户端的标准接口
 */
interface HttpClientInterface
{
    /**
     * 发送 GET 请求
     */
    public function get(string $method, array $parameters = []): TelegramResponse;

    /**
     * 发送 POST 请求
     */
    public function post(string $method, array $parameters = []): TelegramResponse;

    /**
     * 上传文件
     */
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse;

    /**
     * 获取 Bot Token
     */
    public function getToken(): string;

    /**
     * 获取 API 基础 URL
     */
    public function getBaseUrl(): string;

    /**
     * 获取客户端配置
     */
    public function getConfig(): array;

    /**
     * 设置请求超时时间
     */
    public function setTimeout(int $timeout): static;

    /**
     * 设置重试次数
     */
    public function setRetryAttempts(int $attempts): static;

    /**
     * 设置重试延迟
     */
    public function setRetryDelay(int $delay): static;

    /**
     * 获取最后一次请求的响应
     */
    public function getLastResponse(): ?TelegramResponse;

    /**
     * 获取最后一次请求的错误
     */
    public function getLastError(): ?\Throwable;
}
