<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Http\GuzzleHttpClient;
use XBot\Telegram\Http\HttpClientConfig;

/**
 * Bot: minimal entrypoint for common operations.
 *
 * Example:
 * Bot::init($config);
 * Bot::to(123456)->html()->message('<b>Hello</b>');
 * Bot::via('marketing')->to(123456)->message('Hi');
 */
class Bot
{

    /**
     * Create a simple SDK instance by token.
     *
     * This bypasses BotManager and returns a standalone TelegramBot
     * configured with sane defaults. Ideal for single-bot, minimal usage.
     */
    public static function token(string $token): TelegramBot
    {
        $name = 'default';
        $config = HttpClientConfig::fromArray([
            'token' => $token,
        ], $name);

        $client = new GuzzleHttpClient($config);

        return new TelegramBot($name, $client, [
            'token_validation' => [
                'enabled' => true,
                'pattern' => '/^\d+:[a-zA-Z0-9_-]{32,}$/',
            ],
        ]);
    }

    // Intentionally minimal. No BotManager, facades, or chain builders.
}
