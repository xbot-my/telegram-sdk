<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Http\GuzzleHttpClient;
use XBot\Telegram\Http\HttpClientConfig;
use XBot\Telegram\Exceptions\InstanceException;
use XBot\Telegram\Contracts\BotManagerInterface;
use XBot\Telegram\Exceptions\ConfigurationException;

/**
 * Bot 管理器
 *
 * 管理多个 Bot 实例，提供实例隔离和生命周期管理
 */
class BotManager implements BotManagerInterface
{
    /**
     * Bot 实例缓存
     */
    protected array $instances = [];

    /**
     * Bot 配置
     */
    protected array $configs = [];

    /**
     * Token 校验配置
     */
    protected array $tokenValidation = [
        'enabled' => true,
        'pattern' => '/^\d+:[a-zA-Z0-9_-]{32,}$/',
    ];

    /**
     * 默认 Bot 名称
     */
    protected string $defaultBotName;

    /**
     * 管理器创建时间
     */
    protected int $createdAt;

    /**
     * 管理器统计信息
     */
    protected array $stats = [
        'total_bots_created' => 0,
        'total_bots_removed' => 0,
        'total_reload_count' => 0,
        'last_activity_time' => null,
    ];

    public function __construct(array $config = [])
    {
        $this->createdAt = time();
        $this->loadConfiguration($config);
    }

    /**
     * 加载配置
     */
    protected function loadConfiguration(array $config): void
    {
        // 设置默认 Bot 名称
        $this->defaultBotName = $config['default'] ?? 'main';

        // 加载 Bot 配置
        $this->configs = $config['bots'] ?? [];

        // 加载 Token 校验配置
        $this->tokenValidation = array_merge(
            $this->tokenValidation,
            $config['token_validation'] ?? []
        );

        if (empty($this->configs)) {
            throw ConfigurationException::missing('bots configuration');
        }

        if (! isset($this->configs[$this->defaultBotName])) {
            throw ConfigurationException::missing("default bot configuration: {$this->defaultBotName}");
        }
    }

    /**
     * 获取指定名称的 Bot 实例
     */
    public function bot(string $name = null): TelegramBot
    {
        $botName = $name ?? $this->defaultBotName;

        // 从缓存中获取实例
        if (isset($this->instances[$botName])) {
            $this->updateLastActivity();

            return $this->instances[$botName];
        }

        // 创建新实例
        return $this->createBot($botName, $this->getBotConfig($botName));
    }

    /**
     * 创建新的 Bot 实例
     */
    public function createBot(string $name, array $config): TelegramBot
    {
        if (isset($this->instances[$name])) {
            throw InstanceException::alreadyExists($name);
        }

        try {
            // 验证配置并合并 Token 校验选项
            $config['token_validation'] = $this->validateBotConfig($name, $config);

            // 创建 HTTP 客户端配置
            $httpConfig = HttpClientConfig::fromArray($config, $name);

            // 创建 HTTP 客户端
            $httpClient = new GuzzleHttpClient($httpConfig);

            // 创建 Bot 实例
            $bot = new TelegramBot($name, $httpClient, $config);

            // 缓存实例
            $this->instances[$name] = $bot;
            $this->configs[$name]   = $config;

            // 更新统计
            $this->stats['total_bots_created']++;
            $this->updateLastActivity();

            return $bot;
        } catch (\Throwable $e) {
            throw InstanceException::createFailed($name, $e->getMessage());
        }
    }

    /**
     * 检查 Bot 实例是否存在
     */
    public function hasBot(string $name): bool
    {
        return isset($this->instances[$name]) || isset($this->configs[$name]);
    }

    /**
     * 移除 Bot 实例
     */
    public function removeBot(string $name): void
    {
        if ($name === $this->defaultBotName) {
            throw InstanceException::createFailed($name, 'Cannot remove default bot instance');
        }

        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
            $this->stats['total_bots_removed']++;
            $this->updateLastActivity();
        }

        if (isset($this->configs[$name])) {
            unset($this->configs[$name]);
        }
    }

    /**
     * 获取所有 Bot 实例
     */
    public function getAllBots(): array
    {
        $bots = [];

        foreach ($this->configs as $name => $config) {
            $bots[$name] = $this->bot($name);
        }

        return $bots;
    }

    /**
     * 获取默认 Bot 实例
     */
    public function getDefaultBot(): TelegramBot
    {
        return $this->bot($this->defaultBotName);
    }

    /**
     * 设置默认 Bot 名称
     */
    public function setDefaultBot(string $name): void
    {
        if (! isset($this->configs[$name])) {
            throw InstanceException::notFound($name);
        }

        $this->defaultBotName = $name;
    }

    /**
     * 获取默认 Bot 名称
     */
    public function getDefaultBotName(): string
    {
        return $this->defaultBotName;
    }

    /**
     * 获取所有 Bot 名称
     */
    public function getBotNames(): array
    {
        return array_keys($this->configs);
    }

    /**
     * 获取 Bot 数量
     */
    public function getBotCount(): int
    {
        return count($this->configs);
    }

    /**
     * 清空所有 Bot 实例
     */
    public function clear(): void
    {
        $this->instances = [];
        $this->updateLastActivity();
    }

    /**
     * 重新加载指定 Bot 实例
     */
    public function reloadBot(string $name): TelegramBot
    {
        if (! isset($this->configs[$name])) {
            throw InstanceException::notFound($name);
        }

        // 移除现有实例
        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }

        // 重新创建实例
        $config = $this->configs[$name];
        $bot    = $this->createBot($name, $config);

        $this->stats['total_reload_count']++;
        $this->updateLastActivity();

        return $bot;
    }

    /**
     * 重新加载所有 Bot 实例
     */
    public function reloadAllBots(): void
    {
        $botNames = array_keys($this->instances);

        $this->clear();

        foreach ($botNames as $name) {
            if (isset($this->configs[$name])) {
                $this->createBot($name, $this->configs[$name]);
            }
        }

        $this->updateLastActivity();
    }

    /**
     * 获取管理器统计信息
     */
    public function getStats(): array
    {
        $uptime = time() - $this->createdAt;

        return array_merge($this->stats, [
            'default_bot'           => $this->defaultBotName,
            'total_bots_configured' => count($this->configs),
            'total_bots_loaded'     => count($this->instances),
            'created_at'            => $this->createdAt,
            'uptime'                => $uptime,
            'uptime_formatted'      => $this->formatUptime($uptime),
            'loaded_bots'           => array_keys($this->instances),
            'configured_bots'       => array_keys($this->configs),
            'memory_usage'          => memory_get_usage(true),
            'memory_peak'           => memory_get_peak_usage(true),
        ]);
    }

    /**
     * 检查所有 Bot 的健康状态
     */
    public function healthCheck(): array
    {
        $results = [];

        foreach ($this->configs as $name => $config) {
            $results[$name] = [
                'name'          => $name,
                'is_loaded'     => isset($this->instances[$name]),
                'is_healthy'    => false,
                'error'         => null,
                'response_time' => null,
            ];

            try {
                $startTime    = microtime(true);
                $bot          = $this->bot($name);
                $isHealthy    = $bot->healthCheck();
                $responseTime = microtime(true) - $startTime;

                $results[$name]['is_healthy']    = $isHealthy;
                $results[$name]['response_time'] = round($responseTime * 1000, 2); // ms

            } catch (\Throwable $e) {
                $results[$name]['error'] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * 获取指定 Bot 的配置
     */
    public function getBotConfig(string $name): array
    {
        if (! isset($this->configs[$name])) {
            throw InstanceException::notFound($name);
        }

        return $this->configs[$name];
    }

    /**
     * 合并 Token 校验配置
     */
    protected function mergeTokenValidation(array $config): array
    {
        return array_merge($this->tokenValidation, $config['token_validation'] ?? []);
    }

    /**
     * 更新 Bot 配置
     */
    public function updateBotConfig(string $name, array $config): void
    {
        $config['token_validation'] = $this->validateBotConfig($name, $config);

        $this->configs[$name] = $config;

        // 如果实例已加载，则重新加载
        if (isset($this->instances[$name])) {
            $this->reloadBot($name);
        }
    }

    /**
     * 添加新的 Bot 配置
     */
    public function addBotConfig(string $name, array $config): void
    {
        if (isset($this->configs[$name])) {
            throw InstanceException::alreadyExists($name);
        }

        $config['token_validation'] = $this->validateBotConfig($name, $config);
        $this->configs[$name]       = $config;
    }

    /**
     * 验证 Bot 配置
     */
    protected function validateBotConfig(string $name, array $config): array
    {
        if (empty($name)) {
            throw ConfigurationException::invalid('name', $name, 'Bot name cannot be empty');
        }

        if (! isset($config['token']) || empty($config['token'])) {
            throw ConfigurationException::missingBotToken($name);
        }

        $tokenValidation = $this->mergeTokenValidation($config);

        if (! empty($tokenValidation['enabled']) && ! preg_match($tokenValidation['pattern'], $config['token'])) {
            throw ConfigurationException::invalidBotToken($config['token'], $name);
        }

        // 验证其他可选配置
        if (isset($config['base_url']) && ! filter_var($config['base_url'], FILTER_VALIDATE_URL)) {
            throw ConfigurationException::invalid('base_url', $config['base_url'], 'Invalid base URL', $name);
        }

        if (isset($config['timeout']) && (! is_int($config['timeout']) || $config['timeout'] < 1)) {
            throw ConfigurationException::invalid('timeout', $config['timeout'], 'Timeout must be a positive integer', $name);
        }

        if (isset($config['retry_attempts']) && (! is_int($config['retry_attempts']) || $config['retry_attempts'] < 0)) {
            throw ConfigurationException::invalid('retry_attempts', $config['retry_attempts'], 'Retry attempts must be a non-negative integer', $name);
        }
        
        return $tokenValidation;
    }

    /**
     * 更新最后活动时间
     */
    protected function updateLastActivity(): void
    {
        $this->stats['last_activity_time'] = time();
    }

    /**
     * 格式化运行时间
     */
    protected function formatUptime(int $seconds): string
    {
        $days    = floor($seconds / 86400);
        $hours   = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs    = $seconds % 60;

        return sprintf('%dd %02dh %02dm %02ds', $days, $hours, $minutes, $secs);
    }

    /**
     * 获取实例缓存信息
     */
    public function getCacheInfo(): array
    {
        $cacheInfo = [];

        foreach ($this->instances as $name => $bot) {
            $cacheInfo[$name] = [
                'name'   => $name,
                'class'  => get_class($bot),
                'memory' => $this->getObjectMemoryUsage($bot),
                'stats'  => $bot->getStats(),
            ];
        }

        return $cacheInfo;
    }

    /**
     * 估算对象内存使用量
     */
    protected function getObjectMemoryUsage(object $obj): int
    {
        $startMemory = memory_get_usage();
        $tmp         = unserialize(serialize($obj));
        $endMemory   = memory_get_usage();
        unset($tmp);

        return $endMemory - $startMemory;
    }

    /**
     * 强制垃圾回收
     */
    public function forceGarbageCollection(): array
    {
        $beforeMemory = memory_get_usage();
        $cycles       = gc_collect_cycles();
        $afterMemory  = memory_get_usage();

        return [
            'cycles_collected' => $cycles,
            'memory_before'    => $beforeMemory,
            'memory_after'     => $afterMemory,
            'memory_freed'     => $beforeMemory - $afterMemory,
        ];
    }

    /**
     * 批量操作：获取所有 Bot 的信息
     */
    public function getAllBotsInfo(): array
    {
        $results = [];

        foreach ($this->configs as $name => $config) {
            try {
                $bot     = $this->bot($name);
                $botInfo = $bot->getMe();

                $results[$name] = [
                    'name'     => $name,
                    'bot_info' => $botInfo->toArray(),
                    'status'   => 'active',
                    'stats'    => $bot->getStats(),
                ];
            } catch (\Throwable $e) {
                $results[$name] = [
                    'name'     => $name,
                    'bot_info' => null,
                    'status'   => 'error',
                    'error'    => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * 批量操作：设置所有 Bot 的 Webhook
     */
    public function setAllWebhooks(string $baseUrl, array $options = []): array
    {
        $results = [];

        foreach ($this->configs as $name => $config) {
            try {
                $bot        = $this->bot($name);
                $webhookUrl = rtrim($baseUrl, '/') . '/webhook/' . $name;

                $success = $bot->setWebhook($webhookUrl, $options);

                $results[$name] = [
                    'name'        => $name,
                    'webhook_url' => $webhookUrl,
                    'success'     => $success,
                    'error'       => null,
                ];
            } catch (\Throwable $e) {
                $results[$name] = [
                    'name'        => $name,
                    'webhook_url' => null,
                    'success'     => false,
                    'error'       => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * 批量操作：删除所有 Bot 的 Webhook
     */
    public function deleteAllWebhooks(bool $dropPendingUpdates = false): array
    {
        $results = [];

        foreach ($this->configs as $name => $config) {
            try {
                $bot     = $this->bot($name);
                $success = $bot->deleteWebhook($dropPendingUpdates);

                $results[$name] = [
                    'name'    => $name,
                    'success' => $success,
                    'error'   => null,
                ];
            } catch (\Throwable $e) {
                $results[$name] = [
                    'name'    => $name,
                    'success' => false,
                    'error'   => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * 获取所有配置（隐藏敏感信息）
     */
    public function getAllConfigs(bool $hideSensitive = true): array
    {
        $configs = $this->configs;

        if ($hideSensitive) {
            foreach ($configs as $name => &$config) {
                if (isset($config['token'])) {
                    $config['token'] = substr($config['token'], 0, 10) . '...';
                }
                if (isset($config['webhook_secret'])) {
                    $config['webhook_secret'] = '***';
                }
            }
        }

        return $configs;
    }

    /**
     * 魔术方法：动态获取 Bot 实例
     */
    public function __get(string $name): TelegramBot
    {
        return $this->bot($name);
    }

    /**
     * 魔术方法：检查 Bot 是否存在
     */
    public function __isset(string $name): bool
    {
        return $this->hasBot($name);
    }

    /**
     * 魔术方法：调用默认 Bot 的方法
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->getDefaultBot()->$method(...$arguments);
    }
}
