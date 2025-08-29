<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Exceptions\ConfigurationException;
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
    protected static ?BotManager $manager = null;

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

    /**
     * Initialize with configuration array compatible with config/telegram.php structure.
     *
     * @deprecated Prefer Bot::token() for simple single-bot usage.
     */
    public static function init(array $config): void
    {
        self::$manager = new BotManager($config);
    }

    /**
     * Provide an existing manager (e.g., from a container).
     *
     * @deprecated Prefer Bot::token() for simple single-bot usage.
     */
    public static function useManager(BotManager $manager): void
    {
        self::$manager = $manager;
    }

    /**
     * Get the underlying manager or throw if not initialized.
     *
     * @deprecated Prefer Bot::token() for simple single-bot usage.
     */
    protected static function manager(): BotManager
    {
        if (! self::$manager) {
            throw ConfigurationException::missing('Bot manager not initialized. Call Bot::init($config) first.');
        }

        return self::$manager;
    }

    /**
     * Get a bot instance.
     *
     * @deprecated Prefer Bot::token() for simple single-bot usage.
     */
    public static function bot(?string $name = null): TelegramBot
    {
        return self::manager()->bot($name);
    }

    /**
     * Shortcut to select a bot for chaining.
     *
     * @deprecated Prefer Bot::token() for simple single-bot usage.
     */
    public static function via(string $name): BotMessage
    {
        return new BotMessage(self::bot($name));
    }

    /**
     * Begin a message chain to a chat using the default bot.
     *
     * @deprecated Prefer Bot::token() and call sendMessage() directly.
     */
    public static function to(int|string $chatId): BotMessage
    {
        return (new BotMessage(self::bot()))->to($chatId);
    }
}
