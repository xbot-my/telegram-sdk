<?php

declare(strict_types=1);

namespace XBot\Telegram\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use XBot\Telegram\Providers\TelegramServiceProvider;

/**
 * 测试基类
 */
abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadConfig();
    }

    /**
     * 获取包服务提供者
     */
    protected function getPackageProviders($app): array
    {
        return [
            TelegramServiceProvider::class,
        ];
    }

    /**
     * 获取包别名
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Telegram' => \XBot\Telegram\Facades\Telegram::class,
        ];
    }

    /**
     * 定义环境设置
     */
    protected function defineEnvironment($app): void
    {
        // 设置测试配置
        $app['config']->set('telegram.default', 'test');

        $app['config']->set('telegram.bots.test', [
            'token' => '1234567890:AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPqrstuvw',
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
        ]);

        $app['config']->set('telegram.bots.test2', [
            'token' => '9876543210:ZZYYXXWWVVUUTTSSRRQQPPOONNMMllkkjjhhgg',
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 15,
            'retry_attempts' => 2,
            'retry_delay' => 500,
        ]);
        
        // 设置全局配置
        $app['config']->set('telegram.global', [
            'user_agent' => 'XBot-Telegram-SDK-Test/1.0',
            'verify_ssl' => true,
            'proxy' => null,
            'cache' => [
                'enabled' => false,
                'ttl' => 3600,
                'prefix' => 'telegram_test',
            ],
            'debug' => false,
        ]);
    }

    /**
     * 加载配置文件
     */
    protected function loadConfig(): void
    {
        $configPath = __DIR__ . '/../config/telegram.php';
        if (file_exists($configPath)) {
            $this->app['config']->set('telegram', require $configPath);
        }
    }

    /**
     * 创建模拟的 Telegram API 响应
     */
    protected function createMockResponse(array $result, bool $ok = true): array
    {
        return [
            'ok' => $ok,
            'result' => $result,
        ];
    }

    /**
     * 创建模拟的错误响应
     */
    protected function createMockErrorResponse(string $description, int $errorCode = 400, array $parameters = []): array
    {
        return [
            'ok' => false,
            'error_code' => $errorCode,
            'description' => $description,
            'parameters' => $parameters,
        ];
    }

    /**
     * 创建模拟的用户数据
     */
    protected function createMockUser(int $id = 12345, string $firstName = 'Test', bool $isBot = false): array
    {
        return [
            'id' => $id,
            'is_bot' => $isBot,
            'first_name' => $firstName,
            'username' => 'testuser',
            'language_code' => 'en',
        ];
    }

    /**
     * 创建模拟的聊天数据
     */
    protected function createMockChat(int $id = -12345, string $type = 'private'): array
    {
        if ($type === 'private') {
            return [
                'id' => $id,
                'type' => $type,
                'first_name' => 'Test',
                'username' => 'testuser',
            ];
        }

        return [
            'id' => $id,
            'type' => $type,
            'title' => 'Test Group',
        ];
    }

    /**
     * 创建模拟的消息数据
     */
    protected function createMockMessage(
        int $messageId = 1,
        int $chatId = 12345,
        string $text = 'Test message',
        int $userId = 12345
    ): array {
        return [
            'message_id' => $messageId,
            'date' => time(),
            'chat' => $this->createMockChat($chatId),
            'from' => $this->createMockUser($userId),
            'text' => $text,
        ];
    }

    /**
     * 创建模拟的更新数据
     */
    protected function createMockUpdate(int $updateId = 1, array $message = null): array
    {
        return [
            'update_id' => $updateId,
            'message' => $message ?? $this->createMockMessage(),
        ];
    }

    /**
     * 定义真实环境设置（用于集成测试）
     */
    protected function defineRealEnvironment($app): void
    {
        // 设置真实测试配置
        $app['config']->set('telegram.default', 'real_bot');

        $app['config']->set('telegram.bots.real_bot', [
            'token' => env('TELEGRAM_REAL_TEST_TOKEN'),
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
            'webhook_url' => null,
            'webhook_secret' => null,
            'middleware' => [],
            'rate_limit' => [
                'enabled' => false, // 关闭速率限制以便测试
                'max_requests' => 30,
                'per_seconds' => 60,
            ],
            'logging' => [
                'enabled' => false, // 关闭日志以避免干扰测试
                'level' => 'info',
                'channel' => 'telegram',
            ],
        ]);

        // 设置全局配置
        $app['config']->set('telegram.global', [
            'user_agent' => 'XBot-Telegram-SDK-Test/1.0',
            'verify_ssl' => true,
            'proxy' => null,
            'cache' => [
                'enabled' => false, // 禁用缓存以确保测试的一致性
                'ttl' => 3600,
                'prefix' => 'telegram_test',
            ],
            'debug' => false,
        ]);
    }

    /**
     * 检查是否可以运行真实 API 测试
     */
    protected function canRunRealApiTests(): bool
    {
        // 检查环境变量
        $enableRealTests = env('TELEGRAM_ENABLE_REAL_TESTS', false);
        $botToken = env('TELEGRAM_REAL_TEST_TOKEN');
        
        return $enableRealTests && !empty($botToken);
    }

    /**
     * 跳过真实 API 测试（如果未启用）
     */
    protected function skipIfRealApiTestsDisabled(): void
    {
        if (!$this->canRunRealApiTests()) {
            $this->markTestSkipped('Real API tests are disabled. Set TELEGRAM_ENABLE_REAL_TESTS=true to enable.');
        }
    }

    /**
     * 获取真实测试 Bot Token
     */
    protected function getRealTestToken(): string
    {
        return (string) env('TELEGRAM_REAL_TEST_TOKEN');
    }

    /**
     * 创建测试专用的 Webhook URL
     */
    protected function createTestWebhookUrl(): string
    {
        return 'https://webhook.site/' . bin2hex(random_bytes(16));
    }

    /**
     * 等待 API 调用间隔（避免触发速率限制）
     */
    protected function waitForApiCall(int $milliseconds = 1000): void
    {
        usleep($milliseconds * 1000);
    }

    /**
     * 创建用于真实测试的配置
     */
    protected function createRealTestConfig(): array
    {
        return [
            'token' => $this->getRealTestToken(),
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 30,
            'retry_attempts' => 2,
            'retry_delay' => 500,
        ];
    }
}
