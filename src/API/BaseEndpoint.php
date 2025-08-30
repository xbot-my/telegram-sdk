<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

abstract class BaseEndpoint
{
    protected ClientContract $httpClient;
    protected string $botName;

    public function __construct(ClientContract $httpClient, string $botName)
    {
        $this->httpClient = $httpClient;
        $this->botName = $botName;
    }

    abstract public function __invoke(...$args): mixed;

    protected function call(string $method, array $parameters = []): TelegramResponse
    {
        return $this->httpClient->post($method, $parameters);
    }

    protected function upload(string $method, array $parameters = [], array $files = []): TelegramResponse
    {
        return $this->httpClient->upload($method, $parameters, $files);
    }

    protected function formatResult(mixed $data): \XBot\Telegram\Http\Response\Transformer
    {
        return new \XBot\Telegram\Http\Response\Transformer($data);
    }

    protected function prepareParameters(array $parameters): array
    {
        $prepared = [];
        foreach ($parameters as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_bool($value)) {
                $prepared[$key] = $value ? 'true' : 'false';
            } elseif (is_array($value) || is_object($value)) {
                $prepared[$key] = json_encode($value);
            } else {
                $prepared[$key] = $value;
            }
        }
        return $prepared;
    }

    protected function extractFiles(array &$parameters): array
    {
        $files = [];
        $this->walkFiles($parameters, $files);
        return $files;
    }

    private function walkFiles(array &$parameters, array &$files, string $prefix = ''): void
    {
        foreach ($parameters as $key => &$value) {
            $currentKey = $prefix === '' ? (string)$key : $prefix . '_' . $key;
            if (is_array($value)) {
                $this->walkFiles($value, $files, $currentKey);
            } elseif ((is_string($value) || is_resource($value)) && $this->isFilePath((string)$value)) {
                $files[$currentKey] = $value;
                $value = "attach://{$currentKey}";
            }
        }
    }

    protected function isFilePath(string $value): bool
    {
        if (file_exists($value)) {
            return true;
        }
        if (is_resource($value)) {
            return true;
        }
        if (filter_var($value, FILTER_VALIDATE_URL) || preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            return false;
        }
        return false;
    }

    protected function validateChatId(int|string $chatId): void
    {
        if (empty($chatId)) {
            throw new \InvalidArgumentException('Chat ID cannot be empty');
        }
        if (is_string($chatId) && !str_starts_with($chatId, '@')) {
            throw new \InvalidArgumentException('String chat ID must start with @');
        }
    }

    protected function validateMessageId(int $messageId): void
    {
        if ($messageId <= 0) {
            throw new \InvalidArgumentException('Message ID must be a positive integer');
        }
    }

    protected function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID must be a positive integer');
        }
    }

    protected function validateTextLength(string $text, int $maxLength = 4096): void
    {
        if (strlen($text) > $maxLength) {
            throw new \InvalidArgumentException("Text length cannot exceed {$maxLength} characters");
        }
    }

    protected function validateRequired(array $parameters, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($parameters[$field]) || $parameters[$field] === null || $parameters[$field] === '') {
                throw new \InvalidArgumentException("Required parameter '{$field}' is missing or empty");
            }
        }
    }

    protected function validateUrl(string $url, bool $requireHttps = false): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }
        if ($requireHttps && !str_starts_with($url, 'https://')) {
            throw new \InvalidArgumentException('URL must use HTTPS protocol');
        }
    }

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
