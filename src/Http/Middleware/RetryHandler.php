<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 重试处理中间件
 * 
 * 处理请求失败时的重试逻辑
 */
class RetryHandler
{
    /**
     * 最大重试次数
     */
    private int $maxAttempts;

    /**
     * 基础延迟时间（毫秒）
     */
    private int $baseDelay;

    /**
     * 最大延迟时间（毫秒）
     */
    private int $maxDelay;

    /**
     * 延迟倍数
     */
    private float $delayMultiplier;

    /**
     * 是否使用指数退避
     */
    private bool $useExponentialBackoff;

    /**
     * 是否添加随机抖动
     */
    private bool $useJitter;

    /**
     * 可重试的HTTP状态码
     */
    private array $retryableStatusCodes;

    /**
     * 可重试的异常类型
     */
    private array $retryableExceptions;

    /**
     * 自定义重试条件回调
     */
    private ?\Closure $retryCondition;

    /**
     * 重试前回调
     */
    private ?\Closure $beforeRetry;

    public function __construct(
        int $maxAttempts = 3,
        int $baseDelay = 1000,
        int $maxDelay = 30000,
        float $delayMultiplier = 2.0,
        bool $useExponentialBackoff = true,
        bool $useJitter = true,
        array $retryableStatusCodes = [429, 500, 502, 503, 504],
        array $retryableExceptions = []
    ) {
        $this->maxAttempts = max(1, $maxAttempts);
        $this->baseDelay = max(0, $baseDelay);
        $this->maxDelay = max($baseDelay, $maxDelay);
        $this->delayMultiplier = max(1.0, $delayMultiplier);
        $this->useExponentialBackoff = $useExponentialBackoff;
        $this->useJitter = $useJitter;
        $this->retryableStatusCodes = $retryableStatusCodes;
        $this->retryableExceptions = array_merge($retryableExceptions, [
            \GuzzleHttp\Exception\ConnectException::class,
            \GuzzleHttp\Exception\RequestException::class,
        ]);
        $this->retryCondition = null;
        $this->beforeRetry = null;
    }

    /**
     * 检查是否应该重试
     */
    public function shouldRetry(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?\Throwable $exception = null,
        int $attemptNumber = 1
    ): bool {
        // 如果已达到最大重试次数，不再重试
        if ($attemptNumber >= $this->maxAttempts) {
            return false;
        }

        // 如果有自定义重试条件，优先使用
        if ($this->retryCondition !== null) {
            return ($this->retryCondition)($request, $response, $exception, $attemptNumber);
        }

        // 检查响应状态码
        if ($response !== null) {
            $statusCode = $response->getStatusCode();
            
            // 2xx 状态码表示成功，不需要重试
            if ($statusCode >= 200 && $statusCode < 300) {
                return false;
            }

            // 检查是否为可重试的状态码
            if (in_array($statusCode, $this->retryableStatusCodes)) {
                return true;
            }

            // 4xx 客户端错误（除429外）通常不需要重试
            if ($statusCode >= 400 && $statusCode < 500 && $statusCode !== 429) {
                return false;
            }

            // 5xx 服务器错误可以重试
            if ($statusCode >= 500) {
                return true;
            }
        }

        // 检查异常类型
        if ($exception !== null) {
            foreach ($this->retryableExceptions as $exceptionClass) {
                if ($exception instanceof $exceptionClass) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 计算延迟时间
     */
    public function calculateDelay(
        int $attemptNumber,
        ?ResponseInterface $response = null,
        ?\Throwable $exception = null
    ): int {
        // 如果响应包含 Retry-After 头，优先使用
        if ($response !== null && $response->hasHeader('Retry-After')) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            if (is_numeric($retryAfter)) {
                return (int) $retryAfter * 1000; // 转换为毫秒
            }
        }

        // 计算基础延迟
        $delay = $this->baseDelay;

        if ($this->useExponentialBackoff) {
            // 指数退避：delay = baseDelay * (multiplier ^ (attemptNumber - 1))
            $delay = (int) ($this->baseDelay * pow($this->delayMultiplier, $attemptNumber - 1));
        } else {
            // 线性增长：delay = baseDelay * attemptNumber
            $delay = $this->baseDelay * $attemptNumber;
        }

        // 限制最大延迟
        $delay = min($delay, $this->maxDelay);

        // 添加随机抖动以避免雷群效应
        if ($this->useJitter) {
            $jitter = (int) ($delay * 0.1 * (mt_rand() / mt_getrandmax()));
            $delay += mt_rand(0, 1) ? $jitter : -$jitter;
        }

        return max(0, $delay);
    }

    /**
     * 执行延迟
     */
    public function delay(int $milliseconds): void
    {
        if ($milliseconds > 0) {
            usleep($milliseconds * 1000); // usleep 使用微秒
        }
    }

    /**
     * 获取重试原因描述
     */
    public function getRetryReason(
        ?ResponseInterface $response = null,
        ?\Throwable $exception = null
    ): string {
        if ($response !== null) {
            $statusCode = $response->getStatusCode();
            return match ($statusCode) {
                429 => 'Rate limit exceeded',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable',
                504 => 'Gateway timeout',
                default => "HTTP {$statusCode} error"
            };
        }

        if ($exception !== null) {
            return match (get_class($exception)) {
                \GuzzleHttp\Exception\ConnectException::class => 'Connection failed',
                \GuzzleHttp\Exception\RequestException::class => 'Request failed',
                default => 'Exception: ' . $exception->getMessage()
            };
        }

        return 'Unknown error';
    }

    /**
     * 执行重试前的回调
     */
    public function executeBeforeRetry(
        RequestInterface $request,
        int $attemptNumber,
        int $delay,
        string $reason
    ): void {
        if ($this->beforeRetry !== null) {
            ($this->beforeRetry)($request, $attemptNumber, $delay, $reason);
        }
    }

    /**
     * 设置自定义重试条件
     */
    public function setRetryCondition(?\Closure $condition): self
    {
        $this->retryCondition = $condition;
        return $this;
    }

    /**
     * 设置重试前回调
     */
    public function setBeforeRetry(?\Closure $callback): self
    {
        $this->beforeRetry = $callback;
        return $this;
    }

    /**
     * 添加可重试的状态码
     */
    public function addRetryableStatusCode(int $statusCode): self
    {
        if (!in_array($statusCode, $this->retryableStatusCodes)) {
            $this->retryableStatusCodes[] = $statusCode;
        }
        return $this;
    }

    /**
     * 移除可重试的状态码
     */
    public function removeRetryableStatusCode(int $statusCode): self
    {
        $this->retryableStatusCodes = array_filter(
            $this->retryableStatusCodes,
            fn($code) => $code !== $statusCode
        );
        return $this;
    }

    /**
     * 添加可重试的异常类型
     */
    public function addRetryableException(string $exceptionClass): self
    {
        if (!in_array($exceptionClass, $this->retryableExceptions)) {
            $this->retryableExceptions[] = $exceptionClass;
        }
        return $this;
    }

    /**
     * 移除可重试的异常类型
     */
    public function removeRetryableException(string $exceptionClass): self
    {
        $this->retryableExceptions = array_filter(
            $this->retryableExceptions,
            fn($class) => $class !== $exceptionClass
        );
        return $this;
    }

    /**
     * 设置最大重试次数
     */
    public function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = max(1, $maxAttempts);
        return $this;
    }

    /**
     * 设置基础延迟时间
     */
    public function setBaseDelay(int $baseDelay): self
    {
        $this->baseDelay = max(0, $baseDelay);
        return $this;
    }

    /**
     * 设置最大延迟时间
     */
    public function setMaxDelay(int $maxDelay): self
    {
        $this->maxDelay = max($this->baseDelay, $maxDelay);
        return $this;
    }

    /**
     * 设置延迟倍数
     */
    public function setDelayMultiplier(float $multiplier): self
    {
        $this->delayMultiplier = max(1.0, $multiplier);
        return $this;
    }

    /**
     * 启用或禁用指数退避
     */
    public function setUseExponentialBackoff(bool $use): self
    {
        $this->useExponentialBackoff = $use;
        return $this;
    }

    /**
     * 启用或禁用随机抖动
     */
    public function setUseJitter(bool $use): self
    {
        $this->useJitter = $use;
        return $this;
    }

    /**
     * 获取配置信息
     */
    public function getConfig(): array
    {
        return [
            'max_attempts' => $this->maxAttempts,
            'base_delay' => $this->baseDelay,
            'max_delay' => $this->maxDelay,
            'delay_multiplier' => $this->delayMultiplier,
            'use_exponential_backoff' => $this->useExponentialBackoff,
            'use_jitter' => $this->useJitter,
            'retryable_status_codes' => $this->retryableStatusCodes,
            'retryable_exceptions' => $this->retryableExceptions,
            'has_custom_retry_condition' => $this->retryCondition !== null,
            'has_before_retry_callback' => $this->beforeRetry !== null,
        ];
    }

    /**
     * 获取重试统计信息
     */
    public function getRetryStats(int $totalAttempts, array $attempts): array
    {
        $totalDelay = array_sum(array_column($attempts, 'delay'));
        $successfulAttempt = end($attempts);
        
        return [
            'total_attempts' => $totalAttempts,
            'retry_count' => $totalAttempts - 1,
            'total_delay' => $totalDelay,
            'average_delay' => $totalAttempts > 1 ? $totalDelay / ($totalAttempts - 1) : 0,
            'final_success' => $successfulAttempt['success'] ?? false,
            'attempts' => $attempts,
        ];
    }
}