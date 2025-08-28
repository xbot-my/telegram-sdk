<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

/**
 * 数据传输对象接口
 * 
 * 定义所有 DTO 类的标准接口
 */
interface DTOInterface
{
    /**
     * 从数组创建 DTO 实例
     */
    public static function fromArray(array $data): static;

    /**
     * 填充数据到 DTO 实例
     */
    public function fill(array $data): static;

    /**
     * 将 DTO 转换为数组
     */
    public function toArray(): array;

    /**
     * 将 DTO 转换为 JSON
     */
    public function toJson(int $options = 0): string;

    /**
     * 验证 DTO 数据
     */
    public function validate(): void;

    /**
     * 检查 DTO 是否有效
     */
    public function isValid(): bool;

    /**
     * 获取验证错误
     */
    public function getValidationErrors(): array;

    /**
     * JSON 序列化
     */
    public function jsonSerialize(): array;
}