<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('edits message caption and reply markup', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        if ($method === 'editMessageCaption') {
            expect($params['caption'])->toBe('cap');
            return ['ok' => true, 'result' => ['message_id' => 10, 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
        }
        if ($method === 'editMessageReplyMarkup') {
            expect($params['reply_markup'])->toBe(['inline_keyboard' => [[['text' => 'OK', 'callback_data' => 'ok']]]]);
            return ['ok' => true, 'result' => ['message_id' => 11, 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
        }
        return ['ok' => false, 'error_code' => 400, 'description' => 'unexpected'];
    });

    $bot = new TelegramBot('t', $client);
    $bot->editMessageCaption(1, 10, 'cap');
    $bot->editMessageReplyMarkup(1, 11, ['inline_keyboard' => [[['text' => 'OK', 'callback_data' => 'ok']]]]);
    expect(true)->toBeTrue();
});

it('validates empty caption rejects', function () {
    $bot = new TelegramBot('t', new FakeHttpClient());
    expect(fn() => $bot->editMessageCaption(1, 10, ''))
        ->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
});

