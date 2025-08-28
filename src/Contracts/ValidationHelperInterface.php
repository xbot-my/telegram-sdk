<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

/**
 * 验证助手接口
 * 
 * 定义数据验证的标准接口
 */
interface ValidationHelperInterface
{
    /**
     * 验证数据
     */
    public function validate(array $data, array $rules): array;

    /**
     * 检查数据是否有效
     */
    public function isValid(array $data, array $rules): bool;

    /**
     * 获取验证错误
     */
    public function getErrors(): array;

    /**
     * 获取第一个验证错误
     */
    public function getFirstError(): ?string;

    /**
     * 清空验证错误
     */
    public function clearErrors(): static;

    /**
     * 验证必填字段
     */
    public function required(string $field, mixed $value): bool;

    /**
     * 验证字符串长度
     */
    public function length(string $value, int $min = null, int $max = null): bool;

    /**
     * 验证数字范围
     */
    public function numeric(mixed $value, float $min = null, float $max = null): bool;

    /**
     * 验证整数
     */
    public function integer(mixed $value): bool;

    /**
     * 验证浮点数
     */
    public function float(mixed $value): bool;

    /**
     * 验证布尔值
     */
    public function boolean(mixed $value): bool;

    /**
     * 验证数组
     */
    public function array(mixed $value): bool;

    /**
     * 验证字符串
     */
    public function string(mixed $value): bool;

    /**
     * 验证邮箱格式
     */
    public function email(string $value): bool;

    /**
     * 验证 URL 格式
     */
    public function url(string $value): bool;

    /**
     * 验证正则表达式
     */
    public function regex(string $value, string $pattern): bool;

    /**
     * 验证是否在允许的值中
     */
    public function in(mixed $value, array $allowedValues): bool;

    /**
     * 验证是否不在禁止的值中
     */
    public function notIn(mixed $value, array $forbiddenValues): bool;

    /**
     * 验证 Bot Token 格式
     */
    public function botToken(string $token): bool;

    /**
     * 验证聊天 ID
     */
    public function chatId(int|string $chatId): bool;

    /**
     * 验证用户 ID
     */
    public function userId(int $userId): bool;

    /**
     * 验证消息 ID
     */
    public function messageId(int $messageId): bool;

    /**
     * 验证文件 ID
     */
    public function fileId(string $fileId): bool;

    /**
     * 验证回调查询 ID
     */
    public function callbackQueryId(string $callbackQueryId): bool;

    /**
     * 验证内联查询 ID
     */
    public function inlineQueryId(string $inlineQueryId): bool;

    /**
     * 验证消息文本长度
     */
    public function messageText(string $text): bool;

    /**
     * 验证按钮文本长度
     */
    public function buttonText(string $text): bool;

    /**
     * 验证 URL 按钮
     */
    public function urlButton(string $url): bool;

    /**
     * 验证回调按钮数据
     */
    public function callbackData(string $data): bool;

    /**
     * 验证切换内联按钮查询
     */
    public function switchInlineQuery(string $query): bool;

    /**
     * 验证解析模式
     */
    public function parseMode(string $parseMode): bool;

    /**
     * 验证键盘类型
     */
    public function keyboardType(string $type): bool;

    /**
     * 验证文件上传限制
     */
    public function fileUpload(array $file): bool;

    /**
     * 验证图片文件
     */
    public function imageFile(array $file): bool;

    /**
     * 验证视频文件
     */
    public function videoFile(array $file): bool;

    /**
     * 验证音频文件
     */
    public function audioFile(array $file): bool;

    /**
     * 验证文档文件
     */
    public function documentFile(array $file): bool;

    /**
     * 验证贴纸文件
     */
    public function stickerFile(array $file): bool;

    /**
     * 添加自定义验证规则
     */
    public function addRule(string $name, callable $validator): static;

    /**
     * 移除验证规则
     */
    public function removeRule(string $name): static;

    /**
     * 检查是否有验证规则
     */
    public function hasRule(string $name): bool;

    /**
     * 获取所有验证规则
     */
    public function getRules(): array;

    /**
     * 批量验证多个数据集
     */
    public function validateBatch(array $datasets, array $rules): array;

    /**
     * 验证数组中的每个元素
     */
    public function validateArray(array $array, array $rules): array;
}