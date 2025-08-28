<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('sends a text message with options', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('sendMessage');
        expect($params['chat_id'])->toBe(12345);
        expect($params['text'])->toBe('Hello');
        expect($params['parse_mode'])->toBe('HTML');
        return ['ok' => true, 'result' => ['message_id' => 1, 'date' => time(), 'chat' => ['id' => 12345, 'type' => 'private']]];
    });

    $bot = new TelegramBot('test', $client);
    $msg = $bot->message->sendMessage(12345, 'Hello', ['parse_mode' => 'HTML']);
    expect($msg->messageId)->toBe(1);
});

it('uploads a photo when local path provided', function () {
    $tmp = tempnam(sys_get_temp_dir(), 'tg');
    file_put_contents($tmp, 'img');

    $client = new FakeHttpClient(handler: function ($method, $params, $files) use ($tmp) {
        expect($method)->toBe('sendPhoto');
        expect($files)->toHaveKey('photo');
        expect($files['photo'])->toBe($tmp);
        return ['ok' => true, 'result' => ['message_id' => 2, 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });

    $bot = new TelegramBot('test', $client);
    $msg = $bot->message->sendPhoto(1, $tmp);
    expect($msg->messageId)->toBe(2);
});

it('gets chat info', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('getChat');
        expect($params['chat_id'])->toBe('@channel');
        return ['ok' => true, 'result' => ['id' => -1001, 'type' => 'channel']];
    });

    $bot = new TelegramBot('test', $client);
    $chat = $bot->chat->getChat('@channel');
    expect($chat->type)->toBe('channel');
});

