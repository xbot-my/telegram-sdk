<?php

declare(strict_types=1);

use XBot\Telegram\BotManager;
use XBot\Telegram\TelegramBot;
use XBot\Telegram\Exceptions\InstanceException;

it('accepts valid token with default validation', function () {
    $config = [
        'default' => 'main',
        'bots' => [
            'main' => [
                'token' => '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi',
            ],
        ],
    ];

    $manager = new BotManager($config);
    $bot = $manager->bot();

    expect($bot)->toBeInstanceOf(TelegramBot::class);
});

it('throws exception for invalid token format by default', function () {
    $config = [
        'default' => 'main',
        'bots' => [
            'main' => [
                'token' => 'invalid-token',
            ],
        ],
    ];

    $manager = new BotManager($config);

    expect(fn() => $manager->bot())->toThrow(InstanceException::class);
});

it('allows overriding token pattern', function () {
    $config = [
        'default' => 'main',
        'token_validation' => [
            'pattern' => '/^test_token$/',
        ],
        'bots' => [
            'main' => [
                'token' => 'test_token',
            ],
        ],
    ];

    $manager = new BotManager($config);
    $bot = $manager->bot();

    expect($bot->getToken())->toBe('test_token');
});

it('can disable token validation', function () {
    $config = [
        'default' => 'main',
        'token_validation' => [
            'enabled' => false,
        ],
        'bots' => [
            'main' => [
                'token' => 'invalid token',
            ],
        ],
    ];

    $manager = new BotManager($config);
    $bot = $manager->bot();

    expect($bot->getToken())->toBe('invalid token');
});
