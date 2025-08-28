<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Middleware;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 请求日志中间件
 * 
 * 记录所有HTTP请求和响应的详细信息
 */
class RequestLogger
{
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;

    /**
     * 是否记录请求体
     */
    private bool $logRequestBody;

    /**
     * 是否记录响应体
     */
    private bool $logResponseBody;

    /**
     * 是否记录敏感信息（如Token）
     */
    private bool $logSensitiveData;

    /**
     * 最大日志长度
     */
    private int $maxLogLength;

    /**
     * 敏感字段列表
     */
    private array $sensitiveFields;

    public function __construct(
        ?LoggerInterface $logger = null,
        bool $logRequestBody = true,
        bool $logResponseBody = true,
        bool $logSensitiveData = false,
        int $maxLogLength = 10000,
        array $sensitiveFields = ['token', 'password', 'secret', 'key']
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->logRequestBody = $logRequestBody;
        $this->logResponseBody = $logResponseBody;
        $this->logSensitiveData = $logSensitiveData;
        $this->maxLogLength = $maxLogLength;
        $this->sensitiveFields = $sensitiveFields;
    }

    /**
     * 记录请求开始
     */
    public function logRequestStart(RequestInterface $request, array $options = []): void
    {
        $context = [
            'method' => $request->getMethod(),
            'uri' => $this->sanitizeUri((string) $request->getUri()),
            'headers' => $this->sanitizeHeaders($request->getHeaders()),
            'request_id' => $options['request_id'] ?? uniqid('req_'),
            'timestamp' => time(),
        ];

        if ($this->logRequestBody) {
            $body = (string) $request->getBody();
            if ($body) {
                $context['body'] = $this->sanitizeBody($body);
            }
        }

        if (!empty($options)) {
            $context['options'] = $this->sanitizeOptions($options);
        }

        $this->logger->info('HTTP Request Started', $context);
    }

    /**
     * 记录请求完成
     */
    public function logRequestComplete(
        RequestInterface $request,
        ResponseInterface $response,
        float $duration,
        array $options = []
    ): void {
        $context = [
            'method' => $request->getMethod(),
            'uri' => $this->sanitizeUri((string) $request->getUri()),
            'status_code' => $response->getStatusCode(),
            'reason_phrase' => $response->getReasonPhrase(),
            'duration' => round($duration * 1000, 2), // 转换为毫秒
            'request_id' => $options['request_id'] ?? uniqid('req_'),
            'timestamp' => time(),
        ];

        if ($this->logResponseBody) {
            $body = (string) $response->getBody();
            if ($body) {
                $context['response_body'] = $this->sanitizeBody($body);
            }
        }

        $responseHeaders = $response->getHeaders();
        if (!empty($responseHeaders)) {
            $context['response_headers'] = $this->sanitizeHeaders($responseHeaders);
        }

        $level = $response->getStatusCode() >= 400 ? 'error' : 'info';
        $message = $response->getStatusCode() >= 400 ? 'HTTP Request Failed' : 'HTTP Request Completed';

        $this->logger->log($level, $message, $context);
    }

    /**
     * 记录请求错误
     */
    public function logRequestError(
        RequestInterface $request,
        \Throwable $exception,
        float $duration,
        array $options = []
    ): void {
        $context = [
            'method' => $request->getMethod(),
            'uri' => $this->sanitizeUri((string) $request->getUri()),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'duration' => round($duration * 1000, 2),
            'request_id' => $options['request_id'] ?? uniqid('req_'),
            'timestamp' => time(),
        ];

        if ($this->logRequestBody) {
            $body = (string) $request->getBody();
            if ($body) {
                $context['request_body'] = $this->sanitizeBody($body);
            }
        }

        $this->logger->error('HTTP Request Error', $context);
    }

    /**
     * 记录重试尝试
     */
    public function logRetryAttempt(
        RequestInterface $request,
        int $attemptNumber,
        int $maxAttempts,
        ?int $delay = null,
        ?string $reason = null
    ): void {
        $context = [
            'method' => $request->getMethod(),
            'uri' => $this->sanitizeUri((string) $request->getUri()),
            'attempt' => $attemptNumber,
            'max_attempts' => $maxAttempts,
            'timestamp' => time(),
        ];

        if ($delay !== null) {
            $context['delay'] = $delay;
        }

        if ($reason !== null) {
            $context['reason'] = $reason;
        }

        $this->logger->warning('HTTP Request Retry', $context);
    }

    /**
     * 清理URI，移除敏感信息
     */
    private function sanitizeUri(string $uri): string
    {
        if (!$this->logSensitiveData) {
            // 隐藏Token
            $uri = preg_replace('/\/bot\d+:[a-zA-Z0-9_-]+\//', '/bot[TOKEN]/', $uri);
            
            // 隐藏查询参数中的敏感信息
            foreach ($this->sensitiveFields as $field) {
                $uri = preg_replace('/([?&])' . $field . '=[^&]*/', '$1' . $field . '=[HIDDEN]', $uri);
            }
        }

        return $uri;
    }

    /**
     * 清理请求头，移除敏感信息
     */
    private function sanitizeHeaders(array $headers): array
    {
        if (!$this->logSensitiveData) {
            $sanitized = [];
            foreach ($headers as $name => $values) {
                $lowerName = strtolower($name);
                if (in_array($lowerName, ['authorization', 'x-api-key', 'x-secret']) || 
                    str_contains($lowerName, 'token') || 
                    str_contains($lowerName, 'key')) {
                    $sanitized[$name] = ['[HIDDEN]'];
                } else {
                    $sanitized[$name] = $values;
                }
            }
            return $sanitized;
        }

        return $headers;
    }

    /**
     * 清理请求/响应体
     */
    private function sanitizeBody(string $body): string
    {
        // 限制日志长度
        if (strlen($body) > $this->maxLogLength) {
            $body = substr($body, 0, $this->maxLogLength) . '...[TRUNCATED]';
        }

        if (!$this->logSensitiveData) {
            // 尝试解析JSON并隐藏敏感字段
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $decoded = $this->sanitizeArray($decoded);
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }

            // 如果不是JSON，使用正则表达式隐藏敏感信息
            foreach ($this->sensitiveFields as $field) {
                $body = preg_replace('/"' . $field . '"\s*:\s*"[^"]*"/', '"' . $field . '":"[HIDDEN]"', $body);
                $body = preg_replace('/' . $field . '=[^&\s]*/', $field . '=[HIDDEN]', $body);
            }
        }

        return $body;
    }

    /**
     * 递归清理数组中的敏感信息
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (in_array(strtolower($key), $this->sensitiveFields)) {
                $sanitized[$key] = '[HIDDEN]';
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * 清理选项数组
     */
    private function sanitizeOptions(array $options): array
    {
        if (!$this->logSensitiveData) {
            return $this->sanitizeArray($options);
        }
        return $options;
    }

    /**
     * 设置日志记录器
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * 获取日志记录器
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * 设置是否记录请求体
     */
    public function setLogRequestBody(bool $log): self
    {
        $this->logRequestBody = $log;
        return $this;
    }

    /**
     * 设置是否记录响应体
     */
    public function setLogResponseBody(bool $log): self
    {
        $this->logResponseBody = $log;
        return $this;
    }

    /**
     * 设置是否记录敏感数据
     */
    public function setLogSensitiveData(bool $log): self
    {
        $this->logSensitiveData = $log;
        return $this;
    }

    /**
     * 设置最大日志长度
     */
    public function setMaxLogLength(int $length): self
    {
        $this->maxLogLength = max(100, $length);
        return $this;
    }

    /**
     * 添加敏感字段
     */
    public function addSensitiveField(string $field): self
    {
        if (!in_array($field, $this->sensitiveFields)) {
            $this->sensitiveFields[] = strtolower($field);
        }
        return $this;
    }

    /**
     * 移除敏感字段
     */
    public function removeSensitiveField(string $field): self
    {
        $this->sensitiveFields = array_filter(
            $this->sensitiveFields,
            fn($f) => $f !== strtolower($field)
        );
        return $this;
    }

    /**
     * 获取配置信息
     */
    public function getConfig(): array
    {
        return [
            'log_request_body' => $this->logRequestBody,
            'log_response_body' => $this->logResponseBody,
            'log_sensitive_data' => $this->logSensitiveData,
            'max_log_length' => $this->maxLogLength,
            'sensitive_fields' => $this->sensitiveFields,
        ];
    }
}