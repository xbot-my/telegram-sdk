<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Client;

use Illuminate\Support\Str;
use XBot\Telegram\Contracts\Http\Client\Config as ConfigContract;

/**
 * HTTP 客户端配置类
 */
class Config implements ConfigContract
{
    /**
     * Bot Token
     */
    public string $token;

    /**
     * API 基础 URL
     */
    public string $baseUrl;

    /**
     * 请求超时时间（秒）
     */
    public int $timeout;

    /**
     * 重试次数
     */
    public int $retryAttempts;

    /**
     * 重试延迟（毫秒）
     */
    public int $retryDelay;

    /**
     * 是否验证 SSL
     */
    public bool $verifySSL;

    /**
     * 代理设置
     */
    public ?string $proxy;

    /**
     * User-Agent
     */
    public string $userAgent;

    /**
     * 连接超时时间（秒）
     */
    public int $connectTimeout;

    /**
     * 读取超时时间（秒）
     */
    public int $readTimeout;

    /**
     * 最大重定向次数
     */
    public int $maxRedirects;

    /**
     * 是否启用调试模式
     */
    public bool $debug;

    /**
     * 自定义 HTTP 头
     */
    public array $headers;

    /**
     * 中间件
     */
    public array $middleware;

    /**
     * Bot 实例名称
     */
    public ?string $botName;

    /**
     * Token 校验配置
     */
    public array $tokenValidation;

    /**
     * 日志配置
     */
    public array $logging;

    public function __construct(
        string  $token,
        string  $baseUrl = 'https://api.telegram.org/bot',
        int     $timeout = 30,
        int     $retryAttempts = 3,
        int     $retryDelay = 1000,
        bool    $verifySSL = true,
        ?string $proxy = null,
        string  $userAgent = 'XBot-Telegram-SDK/1.0',
        int     $connectTimeout = 10,
        int     $readTimeout = 30,
        int     $maxRedirects = 5,
        bool    $debug = false,
        array   $headers = [],
        array   $middleware = [],
        ?string $botName = null,
        array   $tokenValidation = [],
        array   $logging = []
    )
    {
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
        $this->tokenValidation = array_merge([
            'enabled' => true,
            'pattern' => '/^\d+:[a-zA-Z0-9_-]{32,}$/',
        ], $tokenValidation);

        $this->logging = array_merge([
            'enabled'       => true,
            'suppress_info' => false,
            'channel'       => null,
        ], $logging);
    }

    /**
     * 获取 Bot Token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * 获取 API 基础 URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
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
     * 获取请求超时时间
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * 获取重试次数
     */
    public function getRetryAttempts(): int
    {
        return $this->retryAttempts;
    }

    /**
     * 获取重试延迟
     */
    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    /**
     * 是否验证 SSL
     */
    public function isVerifySSL(): bool
    {
        return $this->verifySSL;
    }

    /**
     * 获取代理设置
     */
    public function getProxy(): ?string
    {
        return $this->proxy;
    }

    /**
     * 获取 User-Agent
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * 获取连接超时时间
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * 获取读取超时时间
     */
    public function getReadTimeout(): int
    {
        return $this->readTimeout;
    }

    /**
     * 获取最大重定向次数
     */
    public function getMaxRedirects(): int
    {
        return $this->maxRedirects;
    }

    /**
     * 是否启用调试模式
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * 获取自定义 HTTP 头
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 获取中间件
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * 获取 Bot 实例名称
     */
    public function getBotName(): ?string
    {
        return $this->botName;
    }

    /**
     * 从数组创建配置
     */
    public static function fromArray(array $config, ?string $botName = null): static
    {
        return new static(
            token: $config['token'] ?? throw new \InvalidArgumentException('Token is required'),
            baseUrl: $config['base_url'] ?? 'https://api.telegram.org/bot',
            timeout: (int)($config['timeout'] ?? 30),
            retryAttempts: (int)($config['retry_attempts'] ?? 3),
            retryDelay: (int)($config['retry_delay'] ?? 1000),
            verifySSL: (bool)($config['verify_ssl'] ?? true),
            proxy: $config['proxy'] ?? null,
            userAgent: $config['user_agent'] ?? 'XBot-Telegram-SDK/1.0',
            connectTimeout: (int)($config['connect_timeout'] ?? 10),
            readTimeout: (int)($config['read_timeout'] ?? 30),
            maxRedirects: (int)($config['max_redirects'] ?? 5),
            debug: (bool)($config['debug'] ?? false),
            headers: $config['headers'] ?? [],
            middleware: $config['middleware'] ?? [],
            botName: $botName,
            tokenValidation: $config['token_validation'] ?? [],
            logging: $config['logging'] ?? []
        );
    }

    /**
     * 验证配置
     */
    public function validate(): void
    {
        if (empty($this->token)) {
            throw new \InvalidArgumentException('Bot token cannot be empty');
        }

        if (!empty($this->tokenValidation['enabled']) && !preg_match($this->tokenValidation['pattern'], $this->token)) {
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
            'token'            => $this->token,
            'base_url'         => $this->baseUrl,
            'timeout'          => $this->timeout,
            'retry_attempts'   => $this->retryAttempts,
            'retry_delay'      => $this->retryDelay,
            'verify_ssl'       => $this->verifySSL,
            'proxy'            => $this->proxy,
            'user_agent'       => $this->userAgent,
            'connect_timeout'  => $this->connectTimeout,
            'read_timeout'     => $this->readTimeout,
            'max_redirects'    => $this->maxRedirects,
            'debug'            => $this->debug,
            'headers'          => $this->headers,
            'middleware'       => $this->middleware,
            'bot_name'         => $this->botName,
            'token_validation' => $this->tokenValidation,
            'logging'          => $this->logging,
            'api_url'          => $this->getApiUrl(),
            'file_api_url'     => $this->getFileApiUrl(),
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

    // Logging getters
    public function isLoggingEnabled(): bool
    {
        return (bool)($this->logging['enabled'] ?? true);
    }

    public function isSuppressInfoLogs(): bool
    {
        return (bool)($this->logging['suppress_info'] ?? false);
    }

    public function getLoggingChannel(): ?string
    {
        $c = $this->logging['channel'] ?? null;

        return $c ? (string)$c : null;
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $config = $this->toArray();
        // 隐藏敏感信息
        $config['token'] = substr($config['token'], 0, 10) . '...';

        return collect($config)->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
