<?php

declare(strict_types=1);

namespace XBot\Telegram\Exceptions;

use Throwable;

/**
 * 配置异常
 * 
 * 当配置错误时抛出
 */
class ConfigurationException extends TelegramException
{
    /**
     * 配置键名
     */
    protected ?string $configKey = null;

    /**
     * 配置值
     */
    protected mixed $configValue = null;

    public function __construct(
        string $message,
        ?string $configKey = null,
        mixed $configValue = null,
        ?Throwable $previous = null,
        array $context = [],
        ?string $botName = null
    ) {
        $this->configKey = $configKey;
        $this->configValue = $configValue;

        $formattedMessage = $this->formatMessage($message, $configKey);

        parent::__construct($formattedMessage, 500, $previous, $context, $botName);
    }

    /**
     * 获取配置键名
     */
    public function getConfigKey(): ?string
    {
        return $this->configKey;
    }

    /**
     * 获取配置值
     */
    public function getConfigValue(): mixed
    {
        return $this->configValue;
    }

    /**
     * 格式化异常消息
     */
    protected function formatMessage(string $message, ?string $configKey): string
    {
        if ($configKey) {
            return "Configuration Error [{$configKey}]: {$message}";
        }

        return "Configuration Error: {$message}";
    }

    /**
     * 将异常转换为数组
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'config_key' => $this->getConfigKey(),
            'config_value' => $this->getConfigValue(),
        ]);
    }

    /**
     * 创建缺失配置异常
     */
    public static function missing(string $configKey, ?string $botName = null): static
    {
        return new static(
            "Required configuration '{$configKey}' is missing",
            $configKey,
            null,
            null,
            [],
            $botName
        );
    }

    /**
     * 创建无效配置异常
     */
    public static function invalid(string $configKey, mixed $configValue, string $reason = '', ?string $botName = null): static
    {
        $message = "Configuration '{$configKey}' is invalid";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new static(
            $message,
            $configKey,
            $configValue,
            null,
            [],
            $botName
        );
    }

    /**
     * 创建 Bot Token 缺失异常
     */
    public static function missingBotToken(?string $botName = null): static
    {
        $key = $botName ? "bots.{$botName}.token" : 'bot.token';
        
        return new static(
            "Bot token is required but not configured",
            $key,
            null,
            null,
            [],
            $botName
        );
    }

    /**
     * 创建无效 Bot Token 异常
     */
    public static function invalidBotToken(string $token, ?string $botName = null): static
    {
        $key = $botName ? "bots.{$botName}.token" : 'bot.token';
        
        return new static(
            "Bot token format is invalid",
            $key,
            $token,
            null,
            [],
            $botName
        );
    }
}

/**
 * 实例管理异常
 * 
 * 当 Bot 实例管理出错时抛出
 */
class InstanceException extends TelegramException
{
    /**
     * 实例名称
     */
    protected ?string $instanceName = null;

    /**
     * 实例类型
     */
    protected ?string $instanceType = null;

    public function __construct(
        string $message,
        ?string $instanceName = null,
        ?string $instanceType = null,
        ?Throwable $previous = null,
        array $context = [],
        ?string $botName = null
    ) {
        $this->instanceName = $instanceName;
        $this->instanceType = $instanceType;

        $formattedMessage = $this->formatMessage($message, $instanceName, $instanceType);

        parent::__construct($formattedMessage, 500, $previous, $context, $botName);
    }

    /**
     * 获取实例名称
     */
    public function getInstanceName(): ?string
    {
        return $this->instanceName;
    }

    /**
     * 获取实例类型
     */
    public function getInstanceType(): ?string
    {
        return $this->instanceType;
    }

    /**
     * 格式化异常消息
     */
    protected function formatMessage(string $message, ?string $instanceName, ?string $instanceType): string
    {
        $prefix = 'Instance Error';
        
        if ($instanceType && $instanceName) {
            $prefix .= " [{$instanceType}:{$instanceName}]";
        } elseif ($instanceName) {
            $prefix .= " [{$instanceName}]";
        } elseif ($instanceType) {
            $prefix .= " [{$instanceType}]";
        }

        return "{$prefix}: {$message}";
    }

    /**
     * 将异常转换为数组
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'instance_name' => $this->getInstanceName(),
            'instance_type' => $this->getInstanceType(),
        ]);
    }

    /**
     * 创建实例不存在异常
     */
    public static function notFound(string $instanceName, string $instanceType = 'Bot'): static
    {
        return new static(
            "{$instanceType} instance '{$instanceName}' not found",
            $instanceName,
            $instanceType,
            null,
            [],
            $instanceName
        );
    }

    /**
     * 创建实例已存在异常
     */
    public static function alreadyExists(string $instanceName, string $instanceType = 'Bot'): static
    {
        return new static(
            "{$instanceType} instance '{$instanceName}' already exists",
            $instanceName,
            $instanceType,
            null,
            [],
            $instanceName
        );
    }

    /**
     * 创建实例创建失败异常
     */
    public static function createFailed(string $instanceName, string $reason = '', string $instanceType = 'Bot'): static
    {
        $message = "Failed to create {$instanceType} instance '{$instanceName}'";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new static(
            $message,
            $instanceName,
            $instanceType,
            null,
            [],
            $instanceName
        );
    }

    /**
     * 创建实例初始化失败异常
     */
    public static function initializationFailed(string $instanceName, string $reason = '', string $instanceType = 'Bot'): static
    {
        $message = "Failed to initialize {$instanceType} instance '{$instanceName}'";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new static(
            $message,
            $instanceName,
            $instanceType,
            null,
            [],
            $instanceName
        );
    }

    /**
     * 创建默认实例未配置异常
     */
    public static function noDefaultInstance(): static
    {
        return new static(
            "No default Bot instance configured",
            null,
            'Bot',
            null,
            [],
            null
        );
    }
}