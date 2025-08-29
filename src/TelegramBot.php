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
    /**
     * One-shot format to be applied to the next call only.
     */
    protected ?string $oneShotFormat = null;

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
        $instance = $this->methods('update');
        return $this->invokeWithTempFormat($instance, function () use ($instance) {
            return $instance->getMe();
        });
    }

    public function methods(string $group): BaseMethodGroup
    {
        return $this->getGroupInstance($group, true);
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
        // Apply as a one-shot format for the next API call
        $this->oneShotFormat = $format;
        return $this;
    }

    public function getReturnFormat(): string
    {
        return $this->returnFormat;
    }

    public function __call(string $method, array $parameters)
    {
        foreach ($this->availableGroups as $group) {
            $instance = $this->getGroupInstance($group, false);
            if (method_exists($instance, $method)) {
                if ($this->oneShotFormat !== null) {
                    $instance->setOneShotReturnFormat($this->oneShotFormat);
                    $this->oneShotFormat = null;
                }
                return $instance->{$method}(...$parameters);
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    /**
     * Resolve a method group instance with optional one-shot consumption.
     */
    private function getGroupInstance(string $group, bool $consumeOneShot): BaseMethodGroup
    {
        $group = strtolower($group);

        if (!in_array($group, $this->availableGroups, true)) {
            throw new \InvalidArgumentException("Unknown method group: {$group}");
        }

        if (!isset($this->methodGroups[$group])) {
            $class = __NAMESPACE__ . '\\Methods\\' . ucfirst($group) . 'Methods';
            $this->methodGroups[$group] = new $class($this->httpClient, $this->name, $this->returnFormat);
        }

        if ($consumeOneShot && $this->oneShotFormat !== null) {
            $this->methodGroups[$group]->setOneShotReturnFormat($this->oneShotFormat);
            $this->oneShotFormat = null;
        }

        return $this->methodGroups[$group];
    }

    /**
     * Invoke a method on a group, temporarily applying one-shot format if present.
     */
    private function invokeWithTempFormat(BaseMethodGroup $instance, callable $fn): mixed
    {
        if ($this->oneShotFormat !== null) {
            $prev = $instance->getReturnFormat();
            $instance->setReturnFormat($this->oneShotFormat);
            try {
                return $fn();
            } finally {
                $instance->setReturnFormat($prev);
                $this->oneShotFormat = null;
            }
        }
        return $fn();
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
