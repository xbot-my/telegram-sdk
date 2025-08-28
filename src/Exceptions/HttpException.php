<?php

declare(strict_types=1);

namespace XBot\Telegram\Exceptions;

use Throwable;

/**
 * HTTP 异常
 * 
 * 当 HTTP 请求失败时抛出
 */
class HttpException extends TelegramException
{
    /**
     * HTTP 状态码
     */
    protected int $statusCode;

    /**
     * HTTP 响应原因短语
     */
    protected string $reasonPhrase;

    /**
     * HTTP 响应头
     */
    protected array $headers = [];

    /**
     * HTTP 响应体
     */
    protected ?string $responseBody = null;

    /**
     * 请求 URL
     */
    protected ?string $requestUrl = null;

    /**
     * 请求方法
     */
    protected ?string $requestMethod = null;

    public function __construct(
        string $message,
        int $statusCode = 0,
        string $reasonPhrase = '',
        array $headers = [],
        ?string $responseBody = null,
        ?string $requestUrl = null,
        ?string $requestMethod = null,
        ?Throwable $previous = null,
        array $context = [],
        ?string $botName = null
    ) {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        $this->headers = $headers;
        $this->responseBody = $responseBody;
        $this->requestUrl = $requestUrl;
        $this->requestMethod = $requestMethod;

        $formattedMessage = $this->formatMessage($message, $statusCode, $reasonPhrase, $requestUrl, $requestMethod);

        parent::__construct($formattedMessage, $statusCode, $previous, $context, $botName);
    }

    /**
     * 获取 HTTP 状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 获取 HTTP 响应原因短语
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * 获取 HTTP 响应头
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 获取指定的响应头
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * 获取 HTTP 响应体
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * 获取请求 URL
     */
    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }

    /**
     * 获取请求方法
     */
    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    /**
     * 检查是否为客户端错误（4xx）
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * 检查是否为服务器错误（5xx）
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * 检查是否为网络超时错误
     */
    public function isTimeout(): bool
    {
        return $this->statusCode === 408 || 
               str_contains(strtolower($this->getMessage()), 'timeout') ||
               str_contains(strtolower($this->getMessage()), 'timed out');
    }

    /**
     * 检查是否为连接错误
     */
    public function isConnectionError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'connection') ||
               str_contains(strtolower($this->getMessage()), 'network') ||
               $this->statusCode === 0;
    }

    /**
     * 检查是否为 SSL/TLS 错误
     */
    public function isSslError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'ssl') ||
               str_contains(strtolower($this->getMessage()), 'tls') ||
               str_contains(strtolower($this->getMessage()), 'certificate');
    }

    /**
     * 格式化异常消息
     */
    protected function formatMessage(
        string $message,
        int $statusCode,
        string $reasonPhrase,
        ?string $requestUrl,
        ?string $requestMethod
    ): string {
        $formatted = "HTTP Request Failed";

        if ($requestMethod && $requestUrl) {
            $formatted .= " [{$requestMethod} {$requestUrl}]";
        }

        if ($statusCode > 0) {
            $formatted .= " [{$statusCode}";
            if ($reasonPhrase) {
                $formatted .= " {$reasonPhrase}";
            }
            $formatted .= "]";
        }

        if ($message) {
            $formatted .= ": {$message}";
        }

        return $formatted;
    }

    /**
     * 将异常转换为数组
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'status_code' => $this->getStatusCode(),
            'reason_phrase' => $this->getReasonPhrase(),
            'headers' => $this->getHeaders(),
            'response_body' => $this->getResponseBody(),
            'request_url' => $this->getRequestUrl(),
            'request_method' => $this->getRequestMethod(),
            'is_client_error' => $this->isClientError(),
            'is_server_error' => $this->isServerError(),
            'is_timeout' => $this->isTimeout(),
            'is_connection_error' => $this->isConnectionError(),
            'is_ssl_error' => $this->isSslError(),
        ]);
    }

    /**
     * 创建超时异常
     */
    public static function timeout(
        string $message = 'Request timeout',
        ?string $requestUrl = null,
        ?string $requestMethod = null,
        ?string $botName = null
    ): static {
        return new static(
            $message,
            408,
            'Request Timeout',
            [],
            null,
            $requestUrl,
            $requestMethod,
            null,
            [],
            $botName
        );
    }

    /**
     * 创建连接异常
     */
    public static function connectionError(
        string $message = 'Connection failed',
        ?string $requestUrl = null,
        ?string $requestMethod = null,
        ?string $botName = null
    ): static {
        return new static(
            $message,
            0,
            'Connection Error',
            [],
            null,
            $requestUrl,
            $requestMethod,
            null,
            [],
            $botName
        );
    }

    /**
     * 创建 SSL/TLS 异常
     */
    public static function sslError(
        string $message = 'SSL/TLS error',
        ?string $requestUrl = null,
        ?string $requestMethod = null,
        ?string $botName = null
    ): static {
        return new static(
            $message,
            0,
            'SSL/TLS Error',
            [],
            null,
            $requestUrl,
            $requestMethod,
            null,
            [],
            $botName
        );
    }
}