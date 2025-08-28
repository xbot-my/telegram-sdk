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

/**
 * HTTP 客户端配置类
 */
class HttpClientConfig
{
    /**
     * Bot Token
     */
    public readonly string $token;

    /**
     * API 基础 URL
     */
    public readonly string $baseUrl;

    /**
     * 请求超时时间（秒）
     */
    public readonly int $timeout;

    /**
     * 重试次数
     */
    public readonly int $retryAttempts;

    /**
     * 重试延迟（毫秒）
     */
    public readonly int $retryDelay;

    /**
     * 是否验证 SSL
     */
    public readonly bool $verifySSL;

    /**
     * 代理设置
     */
    public readonly ?string $proxy;

    /**
     * User-Agent
     */
    public readonly string $userAgent;

    /**
     * 连接超时时间（秒）
     */
    public readonly int $connectTimeout;

    /**
     * 读取超时时间（秒）
     */
    public readonly int $readTimeout;

    /**
     * 最大重定向次数
     */
    public readonly int $maxRedirects;

    /**
     * 是否启用调试模式
     */
    public readonly bool $debug;

    /**
     * 自定义 HTTP 头
     */
    public readonly array $headers;

    /**
     * 中间件
     */
    public readonly array $middleware;

    /**
     * Bot 实例名称
     */
    public readonly ?string $botName;

    public function __construct(
        string $token,
        string $baseUrl = 'https://api.telegram.org/bot',
        int $timeout = 30,
        int $retryAttempts = 3,
        int $retryDelay = 1000,
        bool $verifySSL = true,
        ?string $proxy = null,
        string $userAgent = 'XBot-Telegram-SDK/1.0',
        int $connectTimeout = 10,
        int $readTimeout = 30,
        int $maxRedirects = 5,
        bool $debug = false,
        array $headers = [],
        array $middleware = [],
        ?string $botName = null
    ) {
        $this->token = $token;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = max(1, $timeout);
        $this->retryAttempts = max(0, $retryAttempts);
        $this->retryDelay = max(0, $retryDelay);
        $this->verifySSL = $verifySSL;
        $this->proxy = $proxy;
        $this->userAgent = $userAgent;
        $this->connectTimeout = max(1, $connectTimeout);
        $this->readTimeout = max(1, $readTimeout);
        $this->maxRedirects = max(0, $maxRedirects);
        $this->debug = $debug;
        $this->headers = $headers;
        $this->middleware = $middleware;
        $this->botName = $botName;
    }

    /**
     * 从数组创建配置
     */
    public static function fromArray(array $config, ?string $botName = null): static
    {
        return new static(
            token: $config['token'] ?? throw new \InvalidArgumentException('Token is required'),
            baseUrl: $config['base_url'] ?? 'https://api.telegram.org/bot',
            timeout: (int) ($config['timeout'] ?? 30),
            retryAttempts: (int) ($config['retry_attempts'] ?? 3),
            retryDelay: (int) ($config['retry_delay'] ?? 1000),
            verifySSL: (bool) ($config['verify_ssl'] ?? true),
            proxy: $config['proxy'] ?? null,
            userAgent: $config['user_agent'] ?? 'XBot-Telegram-SDK/1.0',
            connectTimeout: (int) ($config['connect_timeout'] ?? 10),
            readTimeout: (int) ($config['read_timeout'] ?? 30),
            maxRedirects: (int) ($config['max_redirects'] ?? 5),
            debug: (bool) ($config['debug'] ?? false),
            headers: $config['headers'] ?? [],
            middleware: $config['middleware'] ?? [],
            botName: $botName
        );
    }

    /**
     * 获取完整的 API URL
     */
    public function getApiUrl(): string
    {
        return $this->baseUrl . $this->token . '/';
    }

    /**
     * 获取文件 API URL
     */
    public function getFileApiUrl(): string
    {
        return str_replace('/bot', '/file/bot', $this->baseUrl) . $this->token . '/';
    }

    /**
     * 验证配置
     */
    public function validate(): void
    {
        if (empty($this->token)) {
            throw new \InvalidArgumentException('Bot token cannot be empty');
        }

        if (!preg_match('/^\d{8,10}:[a-zA-Z0-9_-]{35}$/', $this->token)) {
            throw new \InvalidArgumentException('Invalid bot token format');
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid base URL');
        }

        if ($this->timeout < 1 || $this->timeout > 300) {
            throw new \InvalidArgumentException('Timeout must be between 1 and 300 seconds');
        }

        if ($this->retryAttempts < 0 || $this->retryAttempts > 10) {
            throw new \InvalidArgumentException('Retry attempts must be between 0 and 10');
        }

        if ($this->proxy && !filter_var($this->proxy, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid proxy URL');
        }
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'base_url' => $this->baseUrl,
            'timeout' => $this->timeout,
            'retry_attempts' => $this->retryAttempts,
            'retry_delay' => $this->retryDelay,
            'verify_ssl' => $this->verifySSL,
            'proxy' => $this->proxy,
            'user_agent' => $this->userAgent,
            'connect_timeout' => $this->connectTimeout,
            'read_timeout' => $this->readTimeout,
            'max_redirects' => $this->maxRedirects,
            'debug' => $this->debug,
            'headers' => $this->headers,
            'middleware' => $this->middleware,
            'bot_name' => $this->botName,
            'api_url' => $this->getApiUrl(),
            'file_api_url' => $this->getFileApiUrl(),
        ];
    }

    /**
     * 创建带有修改的新配置实例
     */
    public function with(array $changes): static
    {
        $config = $this->toArray();
        $config = array_merge($config, $changes);
        
        return static::fromArray($config, $this->botName);
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $config = $this->toArray();
        // 隐藏敏感信息
        $config['token'] = substr($config['token'], 0, 10) . '...';
        
        return json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}