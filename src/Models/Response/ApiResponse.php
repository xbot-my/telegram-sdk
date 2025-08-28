<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\Response;

use XBot\Telegram\Models\DTO\BaseDTO;

/**
 * API 响应基类
 * 
 * 用于统一处理所有 Telegram Bot API 的响应格式
 */
class ApiResponse
{
    /**
     * 响应状态
     */
    public readonly bool $ok;

    /**
     * 响应数据
     */
    public readonly mixed $result;

    /**
     * 错误码（当 ok=false 时）
     */
    public readonly ?int $errorCode;

    /**
     * 错误描述（当 ok=false 时）
     */
    public readonly ?string $description;

    /**
     * 重试时间（当遇到速率限制时，可选）
     */
    public readonly ?int $retryAfter;

    /**
     * 原始响应数据
     */
    public readonly array $rawData;

    /**
     * 响应时间戳
     */
    public readonly int $timestamp;

    /**
     * 响应时长（毫秒）
     */
    public readonly ?int $responseTime;

    /**
     * 请求标识符（用于调试）
     */
    public readonly ?string $requestId;

    public function __construct(
        bool $ok,
        mixed $result = null,
        ?int $errorCode = null,
        ?string $description = null,
        ?int $retryAfter = null,
        array $rawData = [],
        ?int $responseTime = null,
        ?string $requestId = null
    ) {
        $this->ok = $ok;
        $this->result = $result;
        $this->errorCode = $errorCode;
        $this->description = $description;
        $this->retryAfter = $retryAfter;
        $this->rawData = $rawData;
        $this->timestamp = time();
        $this->responseTime = $responseTime;
        $this->requestId = $requestId ?? uniqid('req_');
    }

    /**
     * 从原始 API 响应创建实例
     */
    public static function fromApiResponse(
        array $data, 
        ?int $responseTime = null, 
        ?string $requestId = null
    ): static {
        return new static(
            ok: (bool) ($data['ok'] ?? false),
            result: $data['result'] ?? null,
            errorCode: isset($data['error_code']) ? (int) $data['error_code'] : null,
            description: $data['description'] ?? null,
            retryAfter: isset($data['parameters']['retry_after']) 
                ? (int) $data['parameters']['retry_after'] 
                : null,
            rawData: $data,
            responseTime: $responseTime,
            requestId: $requestId
        );
    }

    /**
     * 创建成功响应
     */
    public static function success(
        mixed $result, 
        ?int $responseTime = null, 
        ?string $requestId = null
    ): static {
        return new static(
            ok: true,
            result: $result,
            rawData: ['ok' => true, 'result' => $result],
            responseTime: $responseTime,
            requestId: $requestId
        );
    }

    /**
     * 创建错误响应
     */
    public static function error(
        int $errorCode,
        string $description,
        ?int $retryAfter = null,
        ?int $responseTime = null,
        ?string $requestId = null
    ): static {
        $rawData = [
            'ok' => false,
            'error_code' => $errorCode,
            'description' => $description,
        ];

        if ($retryAfter !== null) {
            $rawData['parameters'] = ['retry_after' => $retryAfter];
        }

        return new static(
            ok: false,
            errorCode: $errorCode,
            description: $description,
            retryAfter: $retryAfter,
            rawData: $rawData,
            responseTime: $responseTime,
            requestId: $requestId
        );
    }

    /**
     * 检查响应是否成功
     */
    public function isSuccess(): bool
    {
        return $this->ok;
    }

    /**
     * 检查响应是否失败
     */
    public function isError(): bool
    {
        return !$this->ok;
    }

    /**
     * 检查是否有重试时间
     */
    public function hasRetryAfter(): bool
    {
        return $this->retryAfter !== null;
    }

    /**
     * 获取结果数据
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * 获取结果数据作为数组
     */
    public function getResultAsArray(): ?array
    {
        return is_array($this->result) ? $this->result : null;
    }

    /**
     * 获取结果数据作为 DTO 对象
     */
    public function getResultAsDTO(string $dtoClass): ?BaseDTO
    {
        if (!$this->isSuccess() || !is_array($this->result)) {
            return null;
        }

        if (!is_subclass_of($dtoClass, BaseDTO::class)) {
            throw new \InvalidArgumentException("Class {$dtoClass} must extend BaseDTO");
        }

        return $dtoClass::fromArray($this->result);
    }

    /**
     * 获取结果数据作为 DTO 对象数组
     */
    public function getResultAsDTOArray(string $dtoClass): array
    {
        if (!$this->isSuccess() || !is_array($this->result)) {
            return [];
        }

        if (!is_subclass_of($dtoClass, BaseDTO::class)) {
            throw new \InvalidArgumentException("Class {$dtoClass} must extend BaseDTO");
        }

        $dtoArray = [];
        foreach ($this->result as $item) {
            if (is_array($item)) {
                $dtoArray[] = $dtoClass::fromArray($item);
            }
        }

        return $dtoArray;
    }

    /**
     * 获取错误信息
     */
    public function getError(): ?array
    {
        if ($this->isSuccess()) {
            return null;
        }

        return [
            'code' => $this->errorCode,
            'description' => $this->description,
            'retry_after' => $this->retryAfter,
        ];
    }

    /**
     * 获取错误类型
     */
    public function getErrorType(): ?string
    {
        if ($this->errorCode === null) {
            return null;
        }

        return match ($this->errorCode) {
            400 => 'bad_request',
            401 => 'unauthorized', 
            403 => 'forbidden',
            404 => 'not_found',
            409 => 'conflict',
            429 => 'too_many_requests',
            500 => 'internal_server_error',
            502 => 'bad_gateway',
            503 => 'service_unavailable',
            default => 'unknown_error'
        };
    }

    /**
     * 检查是否为速率限制错误
     */
    public function isRateLimitError(): bool
    {
        return $this->errorCode === 429;
    }

    /**
     * 检查是否为认证错误
     */
    public function isAuthError(): bool
    {
        return in_array($this->errorCode, [401, 403]);
    }

    /**
     * 检查是否为网络错误
     */
    public function isNetworkError(): bool
    {
        return in_array($this->errorCode, [500, 502, 503]);
    }

    /**
     * 检查是否为客户端错误
     */
    public function isClientError(): bool
    {
        return $this->errorCode !== null && $this->errorCode >= 400 && $this->errorCode < 500;
    }

    /**
     * 检查是否为服务器错误
     */
    public function isServerError(): bool
    {
        return $this->errorCode !== null && $this->errorCode >= 500;
    }

    /**
     * 检查错误是否可重试
     */
    public function isRetryable(): bool
    {
        if ($this->isSuccess()) {
            return false;
        }

        // 速率限制和服务器错误可以重试
        return $this->isRateLimitError() || $this->isServerError();
    }

    /**
     * 获取响应统计信息
     */
    public function getStats(): array
    {
        return [
            'request_id' => $this->requestId,
            'timestamp' => $this->timestamp,
            'response_time' => $this->responseTime,
            'success' => $this->isSuccess(),
            'error_type' => $this->getErrorType(),
            'retryable' => $this->isRetryable(),
            'has_retry_after' => $this->hasRetryAfter(),
        ];
    }

    /**
     * 获取调试信息
     */
    public function getDebugInfo(): array
    {
        return [
            'request_id' => $this->requestId,
            'timestamp' => $this->timestamp,
            'response_time' => $this->responseTime,
            'ok' => $this->ok,
            'error_code' => $this->errorCode,
            'description' => $this->description,
            'retry_after' => $this->retryAfter,
            'result_type' => gettype($this->result),
            'result_size' => is_array($this->result) ? count($this->result) : null,
            'raw_data_size' => count($this->rawData),
        ];
    }

    /**
     * 应用转换函数到结果
     */
    public function transform(callable $transformer): mixed
    {
        if (!$this->isSuccess()) {
            return null;
        }

        return $transformer($this->result);
    }

    /**
     * 过滤结果数组
     */
    public function filter(callable $filter): ?array
    {
        if (!$this->isSuccess() || !is_array($this->result)) {
            return null;
        }

        return array_filter($this->result, $filter);
    }

    /**
     * 映射结果数组
     */
    public function map(callable $mapper): ?array
    {
        if (!$this->isSuccess() || !is_array($this->result)) {
            return null;
        }

        return array_map($mapper, $this->result);
    }

    /**
     * 获取结果的第一个元素
     */
    public function first(): mixed
    {
        if (!$this->isSuccess() || !is_array($this->result) || empty($this->result)) {
            return null;
        }

        return $this->result[0];
    }

    /**
     * 合并另一个 API 响应
     */
    public function merge(ApiResponse $other): static
    {
        if (!$this->isSuccess() || !$other->isSuccess()) {
            return $this->isSuccess() ? $this : $other;
        }

        $mergedResult = null;
        if (is_array($this->result) && is_array($other->result)) {
            $mergedResult = array_merge($this->result, $other->result);
        } elseif ($this->result !== null) {
            $mergedResult = $this->result;
        } else {
            $mergedResult = $other->result;
        }

        return static::success(
            result: $mergedResult,
            responseTime: ($this->responseTime ?? 0) + ($other->responseTime ?? 0),
            requestId: $this->requestId . '+' . $other->requestId
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'result' => $this->result,
            'error_code' => $this->errorCode,
            'description' => $this->description,
            'retry_after' => $this->retryAfter,
            'timestamp' => $this->timestamp,
            'response_time' => $this->responseTime,
            'request_id' => $this->requestId,
            'stats' => $this->getStats(),
        ];
    }

    /**
     * JSON 序列化
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        if ($this->isSuccess()) {
            $resultInfo = is_array($this->result) 
                ? count($this->result) . ' items'
                : gettype($this->result);
            
            return "Success: {$resultInfo} ({$this->responseTime}ms)";
        }

        return "Error {$this->errorCode}: {$this->description}";
    }
}