<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('edits message text with common options', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('editMessageText');
        expect($params['chat_id'])->toBe(111);
        expect($params['message_id'])->toBe(22);
        expect($params['text'])->toBe('updated');
        expect($params['parse_mode'])->toBe('HTML');
        expect($params['disable_web_page_preview'])->toBe('true');
        return ['ok' => true, 'result' => ['message_id' => 22, 'date' => time(), 'chat' => ['id' => 111, 'type' => 'private']]];
    });

    $bot = new TelegramBot('test', $client);
    $msg = $bot->message->editMessageText(111, 22, 'updated', [
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ]);
    expect($msg->messageId)->toBe(22);
});

it('validates deleteMessage message id must be positive', function () {
    $bot = new TelegramBot('test', new FakeHttpClient());
    expect(fn() => $bot->message->deleteMessage(111, 0))
        ->toThrow(\InvalidArgumentException::class);
});

