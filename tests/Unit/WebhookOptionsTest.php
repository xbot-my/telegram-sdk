<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('sets webhook with common options', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        if ($method === 'setWebhook') {
            expect($params['secret_token'])->toBe('secret');
            expect($params['max_connections'])->toBe(40);
            expect($params['allowed_updates'])->toBe(['message', 'callback_query']);
            return ['ok' => true, 'result' => true];
        }
        return ['ok' => true, 'result' => []];
    });

    $bot = new TelegramBot('test', $client);
    $ok = $bot->setWebhook('https://example/webhook', [
        'secret_token' => 'secret',
        'max_connections' => 40,
        'allowed_updates' => ['message', 'callback_query'],
    ]);
    expect($ok)->toBeTrue();
});

