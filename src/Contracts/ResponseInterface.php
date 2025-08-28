<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

/**
 * 响应接口
 * 
 * 定义所有响应对象的标准接口
 */
interface ResponseInterface extends \JsonSerializable
{
    /**
     * 检查响应是否成功
     */
    public function isOk(): bool;

    /**
     * 检查响应是否失败
     */
    public function isFailed(): bool;

    /**
     * 获取响应状态码
     */
    public function getStatusCode(): int;

    /**
     * 获取响应数据
     */
    public function getResult(): mixed;

    /**
     * 获取错误代码
     */
    public function getErrorCode(): ?int;

    /**
     * 获取错误描述
     */
    public function getDescription(): ?string;

    /**
     * 获取原始响应数据
     */
    public function getRawResponse(): array;

    /**
     * 获取响应头
     */
    public function getHeaders(): array;

    /**
     * 获取特定响应头
     */
    public function getHeader(string $name): ?string;

    /**
     * 检查是否有特定响应头
     */
    public function hasHeader(string $name): bool;

    /**
     * 获取响应大小（字节）
     */
    public function getSize(): int;

    /**
     * 获取响应时间（毫秒）
     */
    public function getResponseTime(): float;

    /**
     * 转换为 DTO 对象
     */
    public function toDTO(string $dtoClass): DTOInterface;

    /**
     * 转换为 DTO 数组
     */
    public function toDTOArray(string $dtoClass): array;

    /**
     * 转换为数组
     */
    public function toArray(): array;

    /**
     * 转换为 JSON
     */
    public function toJson(int $options = 0): string;

    /**
     * 获取响应元数据
     */
    public function getMetadata(): array;

    /**
     * 设置响应元数据
     */
    public function setMetadata(array $metadata): static;

    /**
     * 检查响应是否包含特定键
     */
    public function has(string $key): bool;

    /**
     * 获取响应中的特定值
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * 确保响应成功，否则抛出异常
     */
    public function ensureOk(): static;

    /**
     * 验证响应数据
     */
    public function validate(): void;

    /**
     * 检查响应是否有效
     */
    public function isValid(): bool;

    /**
     * 获取验证错误
     */
    public function getValidationErrors(): array;
}