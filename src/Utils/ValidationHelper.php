<?php

declare(strict_types=1);

namespace XBot\Telegram\Utils;

/**
 * 验证工具类
 * 
 * 提供数据验证相关的实用方法
 */
class ValidationHelper
{
    /**
     * 验证 Telegram Bot Token 格式
     */
    public static function validateBotToken(string $token): bool
    {
        return preg_match('/^\d{8,10}:[a-zA-Z0-9_-]{35}$/', $token) === 1;
    }

    /**
     * 验证聊天 ID
     */
    public static function validateChatId(int|string $chatId): bool
    {
        if (is_int($chatId)) {
            return $chatId !== 0;
        }

        if (is_string($chatId)) {
            return str_starts_with($chatId, '@') && strlen($chatId) > 1;
        }

        return false;
    }

    /**
     * 验证用户 ID
     */
    public static function validateUserId(int $userId): bool
    {
        return $userId > 0;
    }

    /**
     * 验证消息 ID
     */
    public static function validateMessageId(int $messageId): bool
    {
        return $messageId > 0;
    }

    /**
     * 验证文本长度
     */
    public static function validateTextLength(string $text, int $maxLength = 4096): bool
    {
        return strlen($text) <= $maxLength;
    }

    /**
     * 验证 URL 格式
     */
    public static function validateUrl(string $url, bool $requireHttps = false): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if ($requireHttps && !str_starts_with($url, 'https://')) {
            return false;
        }

        return true;
    }

    /**
     * 验证 Email 格式
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证电话号码格式
     */
    public static function validatePhoneNumber(string $phoneNumber): bool
    {
        // 简单的电话号码验证，可以根据需要调整
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phoneNumber) === 1;
    }

    /**
     * 验证坐标范围
     */
    public static function validateCoordinates(float $latitude, float $longitude): bool
    {
        return $latitude >= -90 && $latitude <= 90 && 
               $longitude >= -180 && $longitude <= 180;
    }

    /**
     * 验证文件 ID 格式
     */
    public static function validateFileId(string $fileId): bool
    {
        return !empty($fileId) && preg_match('/^[a-zA-Z0-9_-]+$/', $fileId) === 1;
    }

    /**
     * 验证回调数据长度
     */
    public static function validateCallbackData(string $data): bool
    {
        return strlen($data) <= 64;
    }

    /**
     * 验证内联查询偏移量
     */
    public static function validateInlineQueryOffset(string $offset): bool
    {
        return strlen($offset) <= 64;
    }

    /**
     * 验证贴纸集合名称
     */
    public static function validateStickerSetName(string $name): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $name) === 1;
    }

    /**
     * 验证命令格式
     */
    public static function validateBotCommand(string $command): bool
    {
        return preg_match('/^[a-z][a-z0-9_]{0,31}$/', $command) === 1;
    }

    /**
     * 验证 Webhook URL
     */
    public static function validateWebhookUrl(string $url): bool
    {
        if (empty($url)) {
            return true; // 空 URL 用于删除 webhook
        }

        return self::validateUrl($url, true) && strlen($url) <= 256;
    }

    /**
     * 验证密钥格式
     */
    public static function validateSecretToken(string $token): bool
    {
        return strlen($token) >= 1 && strlen($token) <= 256;
    }

    /**
     * 验证 IP 地址格式
     */
    public static function validateIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证端口号
     */
    public static function validatePort(int $port): bool
    {
        return $port >= 1 && $port <= 65535;
    }

    /**
     * 验证语言代码（ISO 639-1）
     */
    public static function validateLanguageCode(string $code): bool
    {
        return preg_match('/^[a-z]{2}$/', $code) === 1;
    }

    /**
     * 验证货币代码（ISO 4217）
     */
    public static function validateCurrencyCode(string $code): bool
    {
        return preg_match('/^[A-Z]{3}$/', $code) === 1;
    }

    /**
     * 验证金额（以最小货币单位为单位）
     */
    public static function validateAmount(int $amount): bool
    {
        return $amount > 0;
    }

    /**
     * 验证文件大小限制
     */
    public static function validateFileSize(int $size, string $type = 'document'): bool
    {
        $limits = [
            'photo' => 10 * 1024 * 1024,      // 10 MB
            'video' => 50 * 1024 * 1024,      // 50 MB
            'audio' => 50 * 1024 * 1024,      // 50 MB
            'voice' => 1 * 1024 * 1024,       // 1 MB
            'video_note' => 1 * 1024 * 1024,  // 1 MB
            'document' => 50 * 1024 * 1024,   // 50 MB
            'sticker' => 512 * 1024,          // 512 KB
        ];

        $maxSize = $limits[$type] ?? $limits['document'];
        return $size <= $maxSize;
    }

    /**
     * 验证时间戳
     */
    public static function validateTimestamp(int $timestamp): bool
    {
        // 检查时间戳是否在合理范围内（2000年到2100年）
        return $timestamp >= 946684800 && $timestamp <= 4102444800;
    }

    /**
     * 验证 Unicode 字符串
     */
    public static function validateUnicodeString(string $string): bool
    {
        return mb_check_encoding($string, 'UTF-8');
    }

    /**
     * 验证 HTML 标签
     */
    public static function validateHtmlTags(string $html, array $allowedTags = []): bool
    {
        if (empty($allowedTags)) {
            $allowedTags = ['b', 'i', 'u', 's', 'strong', 'em', 'code', 'pre', 'a'];
        }

        // 移除允许的标签
        $stripped = $html;
        foreach ($allowedTags as $tag) {
            $stripped = preg_replace("/<\/?{$tag}[^>]*>/i", '', $stripped);
        }

        // 检查是否还有其他 HTML 标签
        return !preg_match('/<[^>]+>/', $stripped);
    }

    /**
     * 验证 JSON 格式
     */
    public static function validateJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 验证数组长度
     */
    public static function validateArrayLength(array $array, int $maxLength): bool
    {
        return count($array) <= $maxLength;
    }

    /**
     * 验证必需字段
     */
    public static function validateRequired(array $data, array $requiredFields): array
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * 验证枚举值
     */
    public static function validateEnum(mixed $value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues, true);
    }

    /**
     * 批量验证
     */
    public static function validateBatch(array $validations): array
    {
        $errors = [];

        foreach ($validations as $field => $rules) {
            foreach ($rules as $rule => $params) {
                $value = $params['value'] ?? null;
                $valid = false;

                switch ($rule) {
                    case 'required':
                        $valid = !empty($value);
                        break;
                    case 'length':
                        $valid = is_string($value) && strlen($value) <= ($params['max'] ?? PHP_INT_MAX);
                        break;
                    case 'url':
                        $valid = self::validateUrl($value, $params['https'] ?? false);
                        break;
                    case 'email':
                        $valid = self::validateEmail($value);
                        break;
                    case 'phone':
                        $valid = self::validatePhoneNumber($value);
                        break;
                    case 'enum':
                        $valid = self::validateEnum($value, $params['values'] ?? []);
                        break;
                }

                if (!$valid) {
                    $errors[$field][] = $rule;
                }
            }
        }

        return $errors;
    }
}