<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('passes common sendMessage options', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('sendMessage');
        expect($params['chat_id'])->toBe(111);
        expect($params['text'])->toBe('Hi');
        expect($params['parse_mode'])->toBe('Markdown');
        expect($params['disable_notification'])->toBeTrue();
        expect($params['reply_to_message_id'])->toBe(5);
        expect($params['reply_markup'])->toBe([ 'inline_keyboard' => [[['text' => 'OK', 'callback_data' => 'ok']]] ]);
        return ['ok' => true, 'result' => ['message_id' => 99, 'date' => time(), 'chat' => ['id' => 111, 'type' => 'private']]];
    });

    $bot = new TelegramBot('test', $client);
    $msg = $bot->sendMessage(111, 'Hi', [
        'parse_mode' => 'Markdown',
        'disable_notification' => true,
        'reply_to_message_id' => 5,
        'reply_markup' => [ 'inline_keyboard' => [[['text' => 'OK', 'callback_data' => 'ok']]] ],
    ]);

    expect($msg->messageId)->toBe(99);
});

