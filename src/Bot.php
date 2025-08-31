<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Contracts\Http\Client;
use XBot\Telegram\Http\Client\Config as ClientConfig;

class Bot extends Telegram
{
    /**
     * Create a simple SDK instance by token.
     */
    public static function token(string $token): self
    {
        static::$token = $token;

        return new static(self::client($token));
    }

    public static function client(string $token): Client
    {
        return new Http\Client\GuzzleHttpClient(
            config: ClientConfig::fromArray(['token' => $token]),
        );
    }
}

/**
 * @alias XBot\Telegram\Bot
 */
class TelegramBot extends Bot
{
}
