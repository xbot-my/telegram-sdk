<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;

use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Models\Response\ResponseFormat;

/**
 * API 方法基础抽象类
 * 
 * 为所有 API 方法分组提供通用功能
 */
abstract class BaseMethodGroup
{
    /**
     * HTTP 客户端
     */
    protected HttpClientInterface $httpClient;

    /**
     * Bot 名称
     */
    protected string $botName;

    /**
     * Result formatting preference.
     */
    protected string $returnFormat = ResponseFormat::ARRAY;

    public function __construct(HttpClientInterface $httpClient, string $botName, string $returnFormat = ResponseFormat::ARRAY)
    {
        $this->httpClient = $httpClient;
        $this->botName = $botName;
        $this->returnFormat = $returnFormat;
    }

    /**
     * 执行 API 调用
     */
    protected function call(string $method, array $parameters = []): TelegramResponse
    {
        return $this->httpClient->post($method, $parameters);
    }

    /**
     * 执行文件上传
     */
    protected function upload(string $method, array $parameters = [], array $files = []): TelegramResponse
    {
        return $this->httpClient->upload($method, $parameters, $files);
    }

    /**
     * 获取 Bot 名称
     */
    public function getBotName(): string
    {
        return $this->botName;
    }

    /**
     * Update return format for this method group.
     */
    public function setReturnFormat(string $format): void
    {
        $this->returnFormat = $format;
    }

    /**
     * Format a result payload according to preference.
     */
    protected function formatResult(mixed $data): mixed
    {
        switch ($this->returnFormat) {
            case ResponseFormat::ARRAY:
                return $data;
            case ResponseFormat::OBJECT:
                return self::toObject($data);
            case ResponseFormat::JSON:
                return json_encode($data, JSON_UNESCAPED_UNICODE);
            case ResponseFormat::COLLECTION:
                if (class_exists('Illuminate\\Support\\Collection')) {
                    return \Illuminate\Support\collect($data);
                }
                throw new \RuntimeException('Collection format requires illuminate/support.');
            default:
                return $data;
        }
    }

    /**
     * Convert array payload recursively to stdClass object(s).
     */
    protected static function toObject(mixed $data): mixed
    {
        if (is_array($data)) {
            // Preserve numeric arrays as lists of objects/values
            if (array_is_list($data)) {
                return array_map([self::class, 'toObject'], $data);
            }

            $obj = new \stdClass();
            foreach ($data as $k => $v) {
                $obj->{$k} = self::toObject($v);
            }
            return $obj;
        }
        return $data;
    }

    /**
     * 提取文件参数
     */
    protected function extractFiles(array &$parameters): array
    {
        $files = [];
        $this->walkFiles($parameters, $files);

        return $files;
    }

    /**
     * 递归遍历参数并提取文件
     */
    private function walkFiles(array &$parameters, array &$files, string $prefix = ''): void
    {
        foreach ($parameters as $key => &$value) {
            $currentKey = $prefix === '' ? (string) $key : $prefix . '_' . $key;

            if (is_array($value)) {
                $this->walkFiles($value, $files, $currentKey);
            } elseif ((is_string($value) || is_resource($value)) && $this->isFilePath((string) $value)) {
                $files[$currentKey] = $value;
                $value = "attach://{$currentKey}";
            }
        }
    }

    /**
     * 检查是否为文件路径
     */
    protected function isFilePath(string $value): bool
    {
        // 检查是否为本地文件路径
        if (file_exists($value)) {
            return true;
        }

        // 检查是否为资源
        if (is_resource($value)) {
            return true;
        }

        // 如果是 URL 或文件 ID，则不是文件路径
        if (filter_var($value, FILTER_VALIDATE_URL) || preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            return false;
        }

        return false;
    }

    /**
     * 准备参数
     */
    protected function prepareParameters(array $parameters): array
    {
        $prepared = [];

        foreach ($parameters as $key => $value) {
            if ($value === null) {
                continue;
            }

            // 将布尔值转换为字符串
            if (is_bool($value)) {
                $prepared[$key] = $value ? 'true' : 'false';
            }
            // 将数组和对象转换为 JSON
            elseif (is_array($value) || is_object($value)) {
                $prepared[$key] = json_encode($value);
            }
            else {
                $prepared[$key] = $value;
            }
        }

        return $prepared;
    }

    /**
     * 验证必填参数
     */
    protected function validateRequired(array $parameters, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($parameters[$field]) || $parameters[$field] === null || $parameters[$field] === '') {
                throw new \InvalidArgumentException("Required parameter '{$field}' is missing or empty");
            }
        }
    }

    /**
     * 验证聊天 ID
     */
    protected function validateChatId(int|string $chatId): void
    {
        if (empty($chatId)) {
            throw new \InvalidArgumentException('Chat ID cannot be empty');
        }

        if (is_string($chatId) && !str_starts_with($chatId, '@')) {
            throw new \InvalidArgumentException('String chat ID must start with @');
        }
    }

    /**
     * 验证用户 ID
     */
    protected function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID must be a positive integer');
        }
    }

    /**
     * 验证消息 ID
     */
    protected function validateMessageId(int $messageId): void
    {
        if ($messageId <= 0) {
            throw new \InvalidArgumentException('Message ID must be a positive integer');
        }
    }

    /**
     * 验证文本长度
     */
    protected function validateTextLength(string $text, int $maxLength = 4096): void
    {
        if (strlen($text) > $maxLength) {
            throw new \InvalidArgumentException("Text length cannot exceed {$maxLength} characters");
        }
    }

    /**
     * 验证 URL 格式
     */
    protected function validateUrl(string $url, bool $requireHttps = false): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        if ($requireHttps && !str_starts_with($url, 'https://')) {
            throw new \InvalidArgumentException('URL must use HTTPS protocol');
        }
    }

    /**
     * 验证坐标
     */
    protected function validateCoordinates(float $latitude, float $longitude): void
    {
        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180');
        }
    }
}
