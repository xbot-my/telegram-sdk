<?php

declare(strict_types=1);

namespace XBot\Telegram\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use XBot\Telegram\Http\Client\GuzzleHttpClient;
use XBot\Telegram\Http\Client\Config as HttpConfig;
use XBot\Telegram\Contracts\Http\Client as HttpClientContract;
use XBot\Telegram\Contracts\Http\Client\Config as HttpConfigContract;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/../../config/telegram.php', 'telegram');

        // Bind contracts to implementations
        $this->app->bind(HttpClientContract::class, GuzzleHttpClient::class);
        $this->app->bind(HttpConfigContract::class, HttpConfig::class);

        // Update dispatcher singleton
        $this->app->singleton(\XBot\Telegram\Utils\UpdateDispatcher::class, function ($app) {
            $handlers = [];
            // Load from config if provided
            $configured = config('telegram.webhook.handlers', []);
            if (is_array($configured)) {
                $handlers = array_values($configured);
            }

            return new \XBot\Telegram\Utils\UpdateDispatcher($handlers, $app);
        });

        // Default Bot singleton (injectable into handlers)
        $this->app->singleton(\XBot\Telegram\Bot::class, function ($app) {
            $token = (string)(config('telegram.token') ?? '');
            $httpConfig = (array)(config('telegram.http') ?? []);
            $httpConfig['token'] = $token;
            $clientConfig = Config::fromArray($httpConfig, config('telegram.name'));
            $client = new GuzzleHttpClient($clientConfig);

            return new \XBot\Telegram\Bot($client, ['name' => (string)(config('telegram.name') ?? 'default')]);
        });
    }

    public function boot(): void
    {
        // Publish config for Laravel apps
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../../config/telegram.php' => config_path('telegram.php'),
            ], 'config');
        }

        // Register route middleware alias
        if ($this->app->bound('router')) {
            $this->app->router->aliasMiddleware('telegram.webhook', \XBot\Telegram\Http\Middleware\VerifyTelegramWebhook::class);
        }

        // Optionally register a default webhook route if Route facade available
        if (class_exists(Route::class)) {
            $prefix = config('telegram.webhook.route_prefix');
            if ($prefix) {
                Route::post($prefix, \XBot\Telegram\Http\Controllers\WebhookController::class)
                     ->name('telegram.webhook')
                     ->middleware(config('telegram.webhook.middleware', []));
            }
        }
    }
}
