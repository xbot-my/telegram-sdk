<?php

declare(strict_types=1);

namespace XBot\Telegram\Exceptions;

use Throwable;

/**
 * 验证异常
 * 
 * 当参数验证失败时抛出
 */
class ValidationException extends TelegramException
{
    /**
     * 验证错误信息
     */
    protected array $errors = [];

    /**
     * 验证失败的字段
     */
    protected ?string $field = null;

    /**
     * 验证失败的值
     */
    protected mixed $value = null;

    /**
     * 验证规则
     */
    protected array $rules = [];

    public function __construct(
        string $message,
        array $errors = [],
        ?string $field = null,
        mixed $value = null,
        array $rules = [],
        ?Throwable $previous = null,
        array $context = [],
        ?string $botName = null
    ) {
        $this->errors = $errors;
        $this->field = $field;
        $this->value = $value;
        $this->rules = $rules;

        $formattedMessage = $this->formatMessage($message, $errors, $field);

        parent::__construct($formattedMessage, 422, $previous, $context, $botName);
    }

    /**
     * 获取验证错误信息
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 获取指定字段的错误信息
     */
    public function getError(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * 获取第一个错误信息
     */
    public function getFirstError(?string $field = null): ?string
    {
        if ($field) {
            $errors = $this->getError($field);
            return $errors[0] ?? null;
        }

        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }

        return null;
    }

    /**
     * 获取验证失败的字段
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * 获取验证失败的值
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * 获取验证规则
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * 检查是否有指定字段的错误
     */
    public function hasError(string $field): bool
    {
        return !empty($this->errors[$field]);
    }

    /**
     * 检查是否有任何错误
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * 添加错误信息
     */
    public function addError(string $field, string $message): static
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    /**
     * 设置错误信息
     */
    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * 格式化异常消息
     */
    protected function formatMessage(string $message, array $errors, ?string $field): string
    {
        if ($field && !empty($errors[$field])) {
            return "{$message} | Field: {$field} | Errors: " . implode(', ', $errors[$field]);
        }

        if (!empty($errors)) {
            $errorMessages = [];
            foreach ($errors as $fieldName => $fieldErrors) {
                $errorMessages[] = "{$fieldName}: " . implode(', ', $fieldErrors);
            }
            return "{$message} | Validation errors: " . implode('; ', $errorMessages);
        }

        return $message;
    }

    /**
     * 将异常转换为数组
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'errors' => $this->getErrors(),
            'field' => $this->getField(),
            'value' => $this->getValue(),
            'rules' => $this->getRules(),
            'first_error' => $this->getFirstError(),
        ]);
    }

    /**
     * 创建必填字段异常
     */
    public static function required(string $field, ?string $botName = null): static
    {
        return new static(
            "Validation failed: {$field} is required",
            [$field => ['The ' . $field . ' field is required.']],
            $field,
            null,
            ['required'],
            null,
            [],
            $botName
        );
    }

    /**
     * 创建类型错误异常
     */
    public static function invalidType(string $field, string $expectedType, mixed $actualValue, ?string $botName = null): static
    {
        $actualType = gettype($actualValue);
        
        return new static(
            "Validation failed: {$field} must be of type {$expectedType}, {$actualType} given",
            [$field => ["The {$field} field must be of type {$expectedType}."]],
            $field,
            $actualValue,
            ['type:' . $expectedType],
            null,
            [],
            $botName
        );
    }

    /**
     * 创建长度错误异常
     */
    public static function invalidLength(string $field, int $min, int $max, mixed $actualValue, ?string $botName = null): static
    {
        $actualLength = is_string($actualValue) ? strlen($actualValue) : (is_array($actualValue) ? count($actualValue) : 0);
        
        return new static(
            "Validation failed: {$field} length must be between {$min} and {$max}, actual length is {$actualLength}",
            [$field => ["The {$field} field must be between {$min} and {$max} characters."]],
            $field,
            $actualValue,
            ["min:{$min}", "max:{$max}"],
            null,
            [],
            $botName
        );
    }

    /**
     * 创建范围错误异常
     */
    public static function invalidRange(string $field, mixed $min, mixed $max, mixed $actualValue, ?string $botName = null): static
    {
        return new static(
            "Validation failed: {$field} must be between {$min} and {$max}, actual value is {$actualValue}",
            [$field => ["The {$field} field must be between {$min} and {$max}."]],
            $field,
            $actualValue,
            ["min:{$min}", "max:{$max}"],
            null,
            [],
            $botName
        );
    }

    /**
     * 创建枚举值错误异常
     */
    public static function invalidEnum(string $field, array $allowedValues, mixed $actualValue, ?string $botName = null): static
    {
        $allowedStr = implode(', ', $allowedValues);
        
        return new static(
            "Validation failed: {$field} must be one of [{$allowedStr}], actual value is '{$actualValue}'",
            [$field => ["The {$field} field must be one of: {$allowedStr}."]],
            $field,
            $actualValue,
            ['in:' . $allowedStr],
            null,
            [],
            $botName
        );
    }

    /**
     * 创建格式错误异常
     */
    public static function invalidFormat(string $field, string $format, mixed $actualValue, ?string $botName = null): static
    {
        return new static(
            "Validation failed: {$field} format is invalid, expected format: {$format}",
            [$field => ["The {$field} field format is invalid."]],
            $field,
            $actualValue,
            ['format:' . $format],
            null,
            [],
            $botName
        );
    }
}