<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Exceptions\ConfigurationException;

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
     * Initialize with configuration array compatible with config/telegram.php structure.
     */
    public static function init(array $config): void
    {
        self::$manager = new BotManager($config);
    }

    /**
     * Provide an existing manager (e.g., from a container).
     */
    public static function useManager(BotManager $manager): void
    {
        self::$manager = $manager;
    }

    /**
     * Get the underlying manager or throw if not initialized.
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
     */
    public static function bot(?string $name = null): TelegramBot
    {
        return self::manager()->bot($name);
    }

    /**
     * Shortcut to select a bot for chaining.
     */
    public static function via(string $name): BotMessage
    {
        return new BotMessage(self::bot($name));
    }

    /**
     * Begin a message chain to a chat using the default bot.
     */
    public static function to(int|string $chatId): BotMessage
    {
        return (new BotMessage(self::bot()))->to($chatId);
    }
}
