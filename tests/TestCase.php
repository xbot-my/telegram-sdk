<?php

declare(strict_types=1);

namespace Tests;

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
            'token' => '123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
        ]);

        $app['config']->set('telegram.bots.test2', [
            'token' => '987654321:BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB',
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 15,
            'retry_attempts' => 2,
            'retry_delay' => 500,
        ]);
    }

    /**
     * 加载配置文件
     */
    protected function loadConfig(): void
    {
        $this->app['config']->set('telegram', require __DIR__ . '/../config/telegram.php');
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
}