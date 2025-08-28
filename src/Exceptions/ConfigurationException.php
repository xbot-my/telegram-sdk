<?php

declare( strict_types = 1 );

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
        string     $message,
        ?string    $configKey = null,
        mixed      $configValue = null,
        ?Throwable $previous = null,
        array      $context = [],
        ?string    $botName = null
    )
    {
        $this->configKey   = $configKey;
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
    protected function formatMessage( string $message, ?string $configKey ): string
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
            'config_key'   => $this->getConfigKey(),
            'config_value' => $this->getConfigValue(),
        ]);
    }
    
    /**
     * 创建缺失配置异常
     */
    public static function missing( string $configKey, ?string $botName = null ): static
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
    public static function invalid( string $configKey, mixed $configValue, string $reason = '', ?string $botName = null ): static
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
    public static function missingBotToken( ?string $botName = null ): static
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
    public static function invalidBotToken( string $token, ?string $botName = null ): static
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

