<?php

namespace XBot\Telegram\Exceptions;

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
        string     $message,
        ?string    $instanceName = null,
        ?string    $instanceType = null,
        ?Throwable $previous = null,
        array      $context = [],
        ?string    $botName = null
    )
    {
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
    protected function formatMessage( string $message, ?string $instanceName, ?string $instanceType ): string
    {
        $prefix = 'Instance Error';
        
        if ($instanceType && $instanceName) {
            $prefix .= " [{$instanceType}:{$instanceName}]";
        }
        elseif ($instanceName) {
            $prefix .= " [{$instanceName}]";
        }
        elseif ($instanceType) {
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
    public static function notFound( string $instanceName, string $instanceType = 'Bot' ): static
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
    public static function alreadyExists( string $instanceName, string $instanceType = 'Bot' ): static
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
    public static function createFailed( string $instanceName, string $reason = '', string $instanceType = 'Bot' ): static
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
    public static function initializationFailed( string $instanceName, string $reason = '', string $instanceType = 'Bot' ): static
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
