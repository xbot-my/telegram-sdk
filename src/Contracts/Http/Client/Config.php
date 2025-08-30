<?php

declare( strict_types = 1 );

namespace XBot\Telegram\Contracts\Http\Client;

/**
 * HTTP 客户端配置接口
 *
 * 定义 HTTP 客户端配置的标准接口
 */
interface Config
{
    /**
     * 获取 Bot Token
     */
    public function getToken(): string;
    
    /**
     * 获取 API 基础 URL
     */
    public function getBaseUrl(): string;
    
    /**
     * 获取完整的 API URL
     */
    public function getApiUrl(): string;
    
    /**
     * 获取文件 API URL
     */
    public function getFileApiUrl(): string;
    
    /**
     * 获取请求超时时间
     */
    public function getTimeout(): int;
    
    /**
     * 获取重试次数
     */
    public function getRetryAttempts(): int;
    
    /**
     * 获取重试延迟
     */
    public function getRetryDelay(): int;
    
    /**
     * 是否验证 SSL
     */
    public function isVerifySSL(): bool;
    
    /**
     * 获取代理设置
     */
    public function getProxy(): ?string;
    
    /**
     * 获取 User-Agent
     */
    public function getUserAgent(): string;
    
    /**
     * 获取连接超时时间
     */
    public function getConnectTimeout(): int;
    
    /**
     * 获取读取超时时间
     */
    public function getReadTimeout(): int;
    
    /**
     * 获取最大重定向次数
     */
    public function getMaxRedirects(): int;
    
    /**
     * 是否启用调试模式
     */
    public function isDebug(): bool;
    
    /**
     * 获取自定义 HTTP 头
     */
    public function getHeaders(): array;
    
    /**
     * 获取中间件
     */
    public function getMiddleware(): array;
    
    /**
     * 获取 Bot 实例名称
     */
    public function getBotName(): ?string;
    
    /**
     * 验证配置
     */
    public function validate(): void;
    
    /**
     * 转换为数组
     */
    public function toArray(): array;
    
    /**
     * 创建带有修改的新配置实例
     */
    public function with( array $changes ): static;
}
