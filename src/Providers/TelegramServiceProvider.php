<?php

declare(strict_types=1);

namespace XBot\Telegram\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use XBot\Telegram\BotManager;
use XBot\Telegram\Contracts\BotManagerInterface;

/**
 * Telegram SDK 服务提供者
 * 
 * 注册 Telegram SDK 的服务到 Laravel 容器
 */
class TelegramServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/telegram.php',
            'telegram'
        );

        $this->registerBotManager();
        $this->registerAliases();
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerMiddleware();
    }

    /**
     * 注册 Bot 管理器
     */
    protected function registerBotManager(): void
    {
        $this->app->singleton(BotManagerInterface::class, function (Container $app): BotManagerInterface {
            $config = $app['config']['telegram'];
            return new BotManager($config);
        });

        $this->app->singleton(BotManager::class, function (Container $app): BotManager {
            return $app->make(BotManagerInterface::class);
        });

        $this->app->singleton('telegram', function (Container $app): BotManager {
            return $app->make(BotManagerInterface::class);
        });
    }

    /**
     * 注册别名
     */
    protected function registerAliases(): void
    {
        $this->app->alias(BotManagerInterface::class, 'telegram.manager');
        $this->app->alias(BotManager::class, 'telegram.manager');
    }

    /**
     * 发布配置文件
     */
    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/telegram.php' => config_path('telegram.php'),
            ], 'telegram-config');

            $this->publishes([
                __DIR__ . '/../../config/telegram.php' => config_path('telegram.php'),
            ], 'telegram');
        }
    }

    /**
     * 注册 Artisan 命令
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \XBot\Telegram\Console\Commands\TelegramInfoCommand::class,
                \XBot\Telegram\Console\Commands\TelegramWebhookCommand::class,
                \XBot\Telegram\Console\Commands\TelegramHealthCheckCommand::class,
                \XBot\Telegram\Console\Commands\TelegramStatsCommand::class,
            ]);
        }
    }

    /**
     * 注册路由
     */
    protected function registerRoutes(): void
    {
        if ($this->app->bound('router')) {
            $router = $this->app['router'];
            
            // 注册 Webhook 路由组
            $router->group([
                'prefix' => config('telegram.webhook.route_prefix', 'telegram/webhook'),
                'middleware' => config('telegram.webhook.middleware', ['api']),
                'namespace' => 'XBot\Telegram\Http\Controllers',
            ], function ($router) {
                $router->post('/{botName}', 'WebhookController@handle');
            });
        }
    }

    /**
     * 注册中间件
     */
    protected function registerMiddleware(): void
    {
        if ($this->app->bound('router')) {
            $router = $this->app['router'];
            
            // 注册 Telegram 专用中间件
            $router->aliasMiddleware('telegram.webhook', \XBot\Telegram\Http\Middleware\VerifyWebhookSignature::class);
            $router->aliasMiddleware('telegram.rate_limit', \XBot\Telegram\Http\Middleware\TelegramRateLimit::class);
        }
    }

    /**
     * 获取提供的服务
     */
    public function provides(): array
    {
        return [
            BotManagerInterface::class,
            BotManager::class,
            'telegram',
            'telegram.manager',
        ];
    }
}