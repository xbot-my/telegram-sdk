<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Http\Client\Config as ClientConfig;

class Bot extends Telegram
{
    /**
     * Create a simple SDK instance by token.
     */
    public static function token(string $token): self
    {
        return new self(static::client($token), ['name' => uniqid()]);
    }

    public static function client(string $token): Http\Client\GuzzleHttpClient
    {
        return new Http\Client\GuzzleHttpClient(
            config: ClientConfig::fromArray(['token' => $token]),
        );
    }
}
