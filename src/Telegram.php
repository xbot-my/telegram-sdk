<?php

declare(strict_types=1);

namespace XBot\Telegram;

use Illuminate\Support\Arr;
use XBot\Telegram\Contracts\Http\Client;
use XBot\Telegram\API\BaseEndpoint;
use XBot\Telegram\Http\Response\TelegramResponse;
use XBot\Telegram\Exceptions\ConfigurationException;

abstract class Telegram
{
    protected Client $client;

    protected int $start_at;

    protected string $name = 'default';


    protected array $stats = [
        'total_calls'      => 0,
        'successful_calls' => 0,
        'failed_calls'     => 0,
        'last_call_time'   => null,
        'uptime'           => 0,
    ];

    // Endpoint cache
    protected array $endpoints = [];
    protected array $config       = [];

    public function __construct(Client $client, array $config = [])
    {
        $this->start_at = time();
        $this->config = $config;
        $this->name = $this->config('name');
        $this->client = $client;

        try {
            $this->validateConfiguration();
        }
        catch (ConfigurationException $e) {

        }
    }

    /**
     * @throws ConfigurationException
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->name)) {
            throw ConfigurationException::invalid('name', $this->name, 'Bot name cannot be empty');
        }

        $token = $this->client->getToken();
        if (empty($token)) {
            throw ConfigurationException::missingBotToken($this->name);
        }

        if (!preg_match('/^\d+:[a-zA-Z0-9_-]{32,}$/', $token)) {
            throw ConfigurationException::invalidBotToken($token, $this->name);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getToken(): string
    {
        return $this->client->getToken();
    }

    public function getMe(): mixed
    {
        return $this->endpoint('getMe')();
    }

    public function __call(string $method, array $parameters)
    {
        $endpoint = $this->endpoint($method);
        return $endpoint(...$parameters);
    }

    /**
     * Resolve an API endpoint instance from a method name.
     * Supports both camelCase (getWebhookInfo) and snake_case (get_webhook_info).
     */
    private function endpoint(string $name): BaseEndpoint
    {
        $studly = $this->toStudlyCase($name);
        $class = __NAMESPACE__ . '\\API\\' . $studly;
        if (!class_exists($class)) {
            throw new \BadMethodCallException("API endpoint not found for method: {$name}");
        }
        if (!isset($this->endpoints[$class])) {
            $this->endpoints[$class] = new $class($this->client, $this->name);
        }
        return $this->endpoints[$class];
    }

    /**
     * Convert camelCase or snake_case to StudlyCase.
     */
    private function toStudlyCase(string $name): string
    {
        // Insert spaces before capitals to split camelCase, then replace underscores with spaces
        $spaced = preg_replace('/(?<!^)([A-Z])/', ' $1', $name);
        $spaced = str_replace('_', ' ', (string)$spaced);
        $parts = preg_split('/\s+/', trim((string)$spaced)) ?: [];
        $parts = array_map(static fn($p) => ucfirst(strtolower((string)$p)), $parts);
        return implode('', $parts);
    }

    public function call(string $method, array $parameters = []): TelegramResponse
    {
        $this->stats['total_calls']++;
        $this->stats['last_call_time'] = time();

        try {
            $response = $this->client->post($method, $parameters);
            $response->ensureOk();

            $this->stats['successful_calls']++;

            return $response;
        }
        catch (\Throwable $e) {
            $this->stats['failed_calls']++;
            throw $e;
        }
    }

    public function healthCheck(): bool
    {
        try {
            $this->getMe();

            return true;
        }
        catch (\Throwable) {
            return false;
        }
    }

    public function getFileUrl(string $fileId): string
    {
        $response = $this->call('getFile', ['file_id' => $fileId])->ensureOk();
        $data = $response->getResult();
        $path = is_array($data) ? ($data['file_path'] ?? '') : '';
        return rtrim('https://api.telegram.org/file/bot' . $this->getToken(), '/') . '/' . ltrim((string)$path, '/');
    }

    public function getStats(): array
    {
        $uptime = time() - $this->start_at;

        return array_merge($this->stats, [
            'name'              => $this->name,
            'token'             => substr($this->getToken(), 0, 10) . '...',
            'created_at'        => $this->start_at,
            'uptime'            => $uptime,
            'uptime_formatted'  => $this->formatUptime($uptime),
            'success_rate'      => $this->stats['total_calls'] > 0
                ? ($this->stats['successful_calls'] / $this->stats['total_calls']) * 100
                : 0,
            'http_client_stats' => $this->client->getStats() ?? [],
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

    public function config(string|array|null $key = null, $value = null)
    {
        if (empty($key) && empty($value)) {
            return $this->config;
        }

        if ($key && empty($value)) {
            return Arr::get($this->config, $key);
        }

        if ($key && $value) {
            Arr::set($this->config, $key, $value);
        } else {
            $this->config = $key;
        }

        return $this;
    }
}

