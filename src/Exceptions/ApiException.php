<?php

declare(strict_types=1);

namespace XBot\Telegram\Exceptions;

use Throwable;

/**
 * Telegram API 异常
 * 
 * 当 Telegram API 返回错误响应时抛出
 */
class ApiException extends TelegramException
{
    /**
     * Telegram API 错误代码
     */
    protected int $errorCode;

    /**
     * Telegram API 错误描述
     */
    protected string $description;

    /**
     * Telegram API 错误参数
     */
    protected array $parameters = [];

    /**
     * 重试后的时间戳（针对 429 错误）
     */
    protected ?int $retryAfter = null;

    /**
     * 迁移到群组 ID（针对群组迁移错误）
     */
    protected ?int $migrateToChatId = null;

    public function __construct(
        string $description,
        int $errorCode = 0,
        array $parameters = [],
        ?Throwable $previous = null,
        array $context = [],
        ?string $botName = null
    ) {
        $this->errorCode = $errorCode;
        $this->description = $description;
        $this->parameters = $parameters;

        // 处理特殊参数
        if (isset($parameters['retry_after'])) {
            $this->retryAfter = (int) $parameters['retry_after'];
        }

        if (isset($parameters['migrate_to_chat_id'])) {
            $this->migrateToChatId = (int) $parameters['migrate_to_chat_id'];
        }

        $message = $this->formatMessage($description, $errorCode, $parameters);

        parent::__construct($message, $errorCode, $previous, $context, $botName);
    }

    /**
     * 获取 Telegram API 错误代码
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 获取 Telegram API 错误描述
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * 获取 Telegram API 错误参数
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * 获取重试后的时间戳
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * 获取迁移到的群组 ID
     */
    public function getMigrateToChatId(): ?int
    {
        return $this->migrateToChatId;
    }

    /**
     * 检查是否为速率限制异常
     */
    public function isRateLimited(): bool
    {
        return $this->errorCode === 429;
    }

    /**
     * 检查是否为群组迁移异常
     */
    public function isChatMigrated(): bool
    {
        return $this->migrateToChatId !== null;
    }

    /**
     * 检查是否为权限异常
     */
    public function isForbidden(): bool
    {
        return $this->errorCode === 403;
    }

    /**
     * 检查是否为资源不存在异常
     */
    public function isNotFound(): bool
    {
        return $this->errorCode === 404;
    }

    /**
     * 检查是否为参数错误异常
     */
    public function isBadRequest(): bool
    {
        return $this->errorCode === 400;
    }

    /**
     * 检查是否为认证异常
     */
    public function isUnauthorized(): bool
    {
        return $this->errorCode === 401;
    }

    /**
     * 检查是否为冲突异常
     */
    public function isConflict(): bool
    {
        return $this->errorCode === 409;
    }

    /**
     * 格式化异常消息
     */
    protected function formatMessage(string $description, int $errorCode, array $parameters): string
    {
        $message = "Telegram API Error [{$errorCode}]: {$description}";

        if (!empty($parameters)) {
            $paramStr = json_encode($parameters, JSON_UNESCAPED_UNICODE);
            $message .= " | Parameters: {$paramStr}";
        }

        return $message;
    }

    /**
     * 将异常转换为数组
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'error_code' => $this->getErrorCode(),
            'description' => $this->getDescription(),
            'parameters' => $this->getParameters(),
            'retry_after' => $this->getRetryAfter(),
            'migrate_to_chat_id' => $this->getMigrateToChatId(),
            'is_rate_limited' => $this->isRateLimited(),
            'is_chat_migrated' => $this->isChatMigrated(),
        ]);
    }

    /**
     * 创建速率限制异常
     */
    public static function rateLimited(int $retryAfter, ?string $botName = null): static
    {
        return new static(
            "Too Many Requests: retry after {$retryAfter} seconds",
            429,
            ['retry_after' => $retryAfter],
            null,
            ['retry_after' => $retryAfter],
            $botName
        );
    }

    /**
     * 创建群组迁移异常
     */
    public static function chatMigrated(int $migrateToChatId, ?string $botName = null): static
    {
        return new static(
            "Bad Request: group chat was upgraded to a supergroup chat",
            400,
            ['migrate_to_chat_id' => $migrateToChatId],
            null,
            ['migrate_to_chat_id' => $migrateToChatId],
            $botName
        );
    }

    /**
     * 创建权限异常
     */
    public static function forbidden(string $description = 'Forbidden', ?string $botName = null): static
    {
        return new static($description, 403, [], null, [], $botName);
    }

    /**
     * 创建资源不存在异常
     */
    public static function notFound(string $description = 'Not Found', ?string $botName = null): static
    {
        return new static($description, 404, [], null, [], $botName);
    }

    /**
     * 创建参数错误异常
     */
    public static function badRequest(string $description = 'Bad Request', array $parameters = [], ?string $botName = null): static
    {
        return new static($description, 400, $parameters, null, [], $botName);
    }

    /**
     * 创建认证异常
     */
    public static function unauthorized(string $description = 'Unauthorized', ?string $botName = null): static
    {
        return new static($description, 401, [], null, [], $botName);
    }

    /**
     * 创建冲突异常
     */
    public static function conflict(string $description = 'Conflict', ?string $botName = null): static
    {
        return new static($description, 409, [], null, [], $botName);
    }
}