<?php

declare(strict_types=1);

namespace XBot\Telegram\Exceptions;

use Exception;
use Throwable;

/**
 * Telegram SDK 基础异常类
 * 
 * 所有 Telegram SDK 相关异常的基类
 */
abstract class TelegramException extends Exception
{
    /**
     * 异常上下文信息
     */
    protected array $context = [];

    /**
     * Bot 实例名称
     */
    protected ?string $botName = null;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = [],
        ?string $botName = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->context = $context;
        $this->botName = $botName;
    }

    /**
     * 获取异常上下文信息
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * 设置异常上下文信息
     */
    public function setContext(array $context): static
    {
        $this->context = $context;
        return $this;
    }

    /**
     * 获取 Bot 实例名称
     */
    public function getBotName(): ?string
    {
        return $this->botName;
    }

    /**
     * 设置 Bot 实例名称
     */
    public function setBotName(?string $botName): static
    {
        $this->botName = $botName;
        return $this;
    }

    /**
     * 将异常转换为数组
     */
    public function toArray(): array
    {
        return [
            'exception' => static::class,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->getContext(),
            'bot_name' => $this->getBotName(),
            'trace' => $this->getTraceAsString(),
        ];
    }

    /**
     * 将异常转换为 JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $botInfo = $this->botName ? " [Bot: {$this->botName}]" : '';
        return sprintf(
            '%s: %s in %s:%d%s',
            static::class,
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            $botInfo
        );
    }
}