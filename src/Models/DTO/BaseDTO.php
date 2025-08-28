<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use DateTime;
use DateTimeInterface;
use ReflectionClass;
use ReflectionProperty;
use XBot\Telegram\Exceptions\ValidationException;

/**
 * 数据传输对象基类
 * 
 * 提供通用的数据序列化、反序列化和验证功能
 */
abstract class BaseDTO
{
    /**
     * 从数组创建 DTO 实例
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        $instance->fill($data);
        return $instance;
    }

    /**
     * 填充数据到 DTO 实例
     */
    public function fill(array $data): static
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $snakeCase = $this->camelToSnake($propertyName);

            // 优先使用蛇形命名的键，然后是驼峰命名的键
            $value = $data[$snakeCase] ?? $data[$propertyName] ?? null;

            if ($value !== null) {
                $this->setProperty($property, $value);
            }
        }

        return $this;
    }

    /**
     * 将 DTO 转换为数组
     */
    public function toArray(): array
    {
        $result = [];
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $value = $property->getValue($this);

            if ($value !== null) {
                $result[$this->camelToSnake($propertyName)] = $this->convertValueToArray($value);
            }
        }

        return $result;
    }

    /**
     * 将 DTO 转换为 JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * 验证 DTO 数据
     * 
     * 子类可以重写此方法来实现自定义验证逻辑
     */
    public function validate(): void
    {
        // 基础验证逻辑，子类可以重写
    }

    /**
     * 检查 DTO 是否有效
     */
    public function isValid(): bool
    {
        try {
            $this->validate();
            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * 获取验证错误
     */
    public function getValidationErrors(): array
    {
        try {
            $this->validate();
            return [];
        } catch (ValidationException $e) {
            return $e->getErrors();
        }
    }

    /**
     * 设置属性值
     */
    protected function setProperty(ReflectionProperty $property, mixed $value): void
    {
        $propertyType = $property->getType();
        
        if ($propertyType === null) {
            $property->setValue($this, $value);
            return;
        }

        $typeName = $propertyType->getName();
        
        // 处理联合类型（如 ?int, ?string 等）
        if ($propertyType->allowsNull() && $value === null) {
            $property->setValue($this, null);
            return;
        }

        // 类型转换
        $convertedValue = $this->convertValue($value, $typeName);
        $property->setValue($this, $convertedValue);
    }

    /**
     * 转换值类型
     */
    protected function convertValue(mixed $value, string $typeName): mixed
    {
        return match ($typeName) {
            'int' => is_numeric($value) ? (int) $value : $value,
            'float' => is_numeric($value) ? (float) $value : $value,
            'string' => (string) $value,
            'bool' => is_bool($value) ? $value : (bool) $value,
            'array' => is_array($value) ? $value : [$value],
            DateTime::class, DateTimeInterface::class => $this->convertToDateTime($value),
            default => $this->convertToObject($value, $typeName),
        };
    }

    /**
     * 转换为 DateTime 对象
     */
    protected function convertToDateTime(mixed $value): ?DateTime
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        if (is_int($value)) {
            return (new DateTime())->setTimestamp($value);
        }

        if (is_string($value)) {
            return new DateTime($value);
        }

        return null;
    }

    /**
     * 转换为对象
     */
    protected function convertToObject(mixed $value, string $className): mixed
    {
        // 如果已经是目标类型，直接返回
        if (is_object($value) && $value instanceof $className) {
            return $value;
        }

        // 如果是数组且目标类是 DTO，尝试创建实例
        if (is_array($value) && is_subclass_of($className, BaseDTO::class)) {
            return $className::fromArray($value);
        }

        return $value;
    }

    /**
     * 转换值为数组格式
     */
    protected function convertValueToArray(mixed $value): mixed
    {
        if ($value instanceof BaseDTO) {
            return $value->toArray();
        }

        if ($value instanceof DateTime) {
            return $value->getTimestamp();
        }

        if (is_array($value)) {
            return array_map(fn($item) => $this->convertValueToArray($item), $value);
        }

        return $value;
    }

    /**
     * 驼峰命名转蛇形命名
     */
    protected function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * 蛇形命名转驼峰命名
     */
    protected function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }

    /**
     * 实现 JsonSerializable 接口
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
        return $this->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * 实现 ArrayAccess 接口 - isset
     */
    public function __isset(string $name): bool
    {
        return isset($this->$name);
    }

    /**
     * 实现 ArrayAccess 接口 - get
     */
    public function __get(string $name): mixed
    {
        return $this->$name ?? null;
    }

    /**
     * 实现 ArrayAccess 接口 - set
     */
    public function __set(string $name, mixed $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * 实现 ArrayAccess 接口 - unset
     */
    public function __unset(string $name): void
    {
        if (property_exists($this, $name)) {
            $this->$name = null;
        }
    }

    /**
     * 克隆对象
     */
    public function __clone()
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $value = $property->getValue($this);
            
            if (is_object($value) && $value instanceof BaseDTO) {
                $property->setValue($this, clone $value);
            } elseif (is_array($value)) {
                $clonedArray = [];
                foreach ($value as $key => $item) {
                    $clonedArray[$key] = is_object($item) && $item instanceof BaseDTO ? clone $item : $item;
                }
                $property->setValue($this, $clonedArray);
            }
        }
    }
}