<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Exceptions\ConfigurationException;
use XBot\Telegram\Methods\BaseMethodGroup;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Models\Response\ResponseFormat;

class TelegramBot
{
    protected string $name;
    protected HttpClientInterface $httpClient;
    protected array $config;
    // No DTO cache; prefer simple transport
    protected mixed $botInfo = null;
    protected int $createdAt;
    protected array $stats = [
        'total_calls' => 0,
        'successful_calls' => 0,
        'failed_calls' => 0,
        'last_call_time' => null,
        'uptime' => 0,
    ];

    /** @var array<string, BaseMethodGroup> */
    protected array $methodGroups = [];

    protected array $availableGroups = [
        'admin',
        'chat',
        'file',
        'inline',
        'message',
        'sticker',
        'game',
        'update',
    ];

    protected string $returnFormat = ResponseFormat::ARRAY;

    public function __construct(string $name, HttpClientInterface $httpClient, array $config = [])
    {
        $this->name = $name;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->createdAt = time();

        $this->validateConfiguration();
    }

    protected function validateConfiguration(): void
    {
        if ($this->name === '') {
            throw ConfigurationException::invalid('name', $this->name, 'Bot name cannot be empty');
        }

        $token = $this->httpClient->getToken();
        if ($token === '') {
            throw ConfigurationException::missingBotToken($this->name);
        }

        $tokenValidation = $this->config['token_validation'] ?? [
            'enabled' => true,
            'pattern' => '/^\d+:[a-zA-Z0-9_-]{32,}$/',
        ];

        if (! empty($tokenValidation['enabled']) && ! preg_match($tokenValidation['pattern'], $token)) {
            throw ConfigurationException::invalidBotToken($token, $this->name);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getToken(): string
    {
        return $this->httpClient->getToken();
    }

    public function getMe(): mixed
    {
        // Delegate to update->getMe() to honor current return format
        return $this->methods('update')->getMe();
    }

    public function methods(string $group): BaseMethodGroup
    {
        $group = strtolower($group);

        if (!in_array($group, $this->availableGroups, true)) {
            throw new \InvalidArgumentException("Unknown method group: {$group}");
        }

        if (!isset($this->methodGroups[$group])) {
            $class = __NAMESPACE__ . '\\Methods\\' . ucfirst($group) . 'Methods';
            $this->methodGroups[$group] = new $class($this->httpClient, $this->name, $this->returnFormat);
        } else {
            // Keep existing instances in sync with current format
            $this->methodGroups[$group]->setReturnFormat($this->returnFormat);
        }

        return $this->methodGroups[$group];
    }

    public function __get(string $name): BaseMethodGroup
    {
        return $this->methods($name);
    }

    /**
     * Convenience accessor to chat method group for fluent usage.
     */
    public function chat(): BaseMethodGroup
    {
        return $this->methods('chat');
    }

    /**
     * Set preferred return format for method results.
     */
    public function as(string $format): static
    {
        $this->returnFormat = $format;
        // propagate to already-instantiated groups
        foreach ($this->methodGroups as $group => $instance) {
            $instance->setReturnFormat($format);
        }
        return $this;
    }

    public function getReturnFormat(): string
    {
        return $this->returnFormat;
    }

    public function __call(string $method, array $parameters)
    {
        foreach ($this->availableGroups as $group) {
            $instance = $this->methods($group);
            if (method_exists($instance, $method)) {
                return $instance->{$method}(...$parameters);
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    public function call(string $method, array $parameters = []): TelegramResponse
    {
        $this->stats['total_calls']++;
        $this->stats['last_call_time'] = time();

        try {
            $response = $this->httpClient->post($method, $parameters);
            $response->ensureOk();

            $this->stats['successful_calls']++;
            return $response;
        } catch (\Throwable $e) {
            $this->stats['failed_calls']++;
            throw $e;
        }
    }

    public function healthCheck(): bool
    {
        try {
            $this->getMe();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getStats(): array
    {
        $uptime = time() - $this->createdAt;

        return array_merge($this->stats, [
            'name' => $this->name,
            'token' => substr($this->getToken(), 0, 10) . '...',
            'created_at' => $this->createdAt,
            'uptime' => $uptime,
            'uptime_formatted' => $this->formatUptime($uptime),
            'success_rate' => $this->stats['total_calls'] > 0
                ? ($this->stats['successful_calls'] / $this->stats['total_calls']) * 100
                : 0,
            'http_client_stats' => $this->httpClient->getStats() ?? [],
        ]);
    }

    protected function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%dd %02dh %02dm %02ds', $days, $hours, $minutes, $secs);
    }
}
