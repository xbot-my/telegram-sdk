<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('forwards message with options', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('forwardMessage');
        expect($params['protect_content'])->toBeTrue();
        expect($params['disable_notification'])->toBeTrue();
        return ['ok' => true, 'result' => ['message_id' => 50, 'date' => time(), 'chat' => ['id' => 2, 'type' => 'private']]];
    });

    $bot = new TelegramBot('test', $client);
    $msg = $bot->forwardMessage(2, 1, 10, [
        'protect_content' => true,
        'disable_notification' => true,
    ]);
    expect($msg->messageId)->toBe(50);
});

it('copies message with options', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('copyMessage');
        expect($params['caption'])->toBe('cap');
        expect($params['parse_mode'])->toBe('Markdown');
        return ['ok' => true, 'result' => ['message_id' => 51]];
    });

    $bot = new TelegramBot('test', $client);
    $id = $bot->copyMessage(2, 1, 10, [
        'caption' => 'cap',
        'parse_mode' => 'Markdown',
    ]);
    expect($id)->toBe(51);
});

