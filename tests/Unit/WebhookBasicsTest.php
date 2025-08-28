<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('sets and deletes webhook and gets info', function () {
    $calls = [];
    $client = new FakeHttpClient(handler: function ($method, $params) use (&$calls) {
        $calls[] = $method;
        if ($method === 'setWebhook') {
            expect($params)->toHaveKey('url');
            expect($params['url'])->toStartWith('https://');
            return ['ok' => true, 'result' => true];
        }
        if ($method === 'getWebhookInfo') {
            return ['ok' => true, 'result' => ['url' => 'https://example/webhook', 'pending_update_count' => 0]];
        }
        if ($method === 'deleteWebhook') {
            return ['ok' => true, 'result' => true];
        }
        return ['ok' => false, 'error_code' => 400, 'description' => 'unexpected'];
    });

    $bot = new TelegramBot('test', $client);
    expect($bot->setWebhook('https://example/webhook'))->toBeTrue();
    $info = $bot->getWebhookInfo();
    expect($info['url'])->toBe('https://example/webhook');
    expect($bot->deleteWebhook(true))->toBeTrue();

    expect($calls)->toContain('setWebhook');
    expect($calls)->toContain('getWebhookInfo');
    expect($calls)->toContain('deleteWebhook');
});

it('validates webhook url format', function () {
    $bot = new TelegramBot('test', new FakeHttpClient());
    expect(fn() => $bot->setWebhook('http://insecure/webhook'))
        ->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
});

