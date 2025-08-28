<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('performs admin operations successfully', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        return ['ok' => true, 'result' => true];
    });

    $bot = new TelegramBot('test', $client);

    expect($bot->banChatMember(1, 2))->toBeTrue();
    expect($bot->unbanChatMember(1, 2))->toBeTrue();
    expect($bot->restrictChatMember(1, 2, ['can_send_messages' => false]))->toBeTrue();
    expect($bot->promoteChatMember(1, 2, ['can_manage_chat' => true]))->toBeTrue();
});

it('answers callback query and inline query', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        if ($method === 'answerCallbackQuery') {
            expect($params)->toHaveKey('callback_query_id');
        }
        if ($method === 'answerInlineQuery') {
            expect($params)->toHaveKeys(['inline_query_id', 'results']);
        }
        return ['ok' => true, 'result' => true];
    });

    $bot = new TelegramBot('test', $client);
    expect($bot->answerCallbackQuery('cb_1'))->toBeTrue();
    expect($bot->answerInlineQuery('iq_1', []))->toBeTrue();
});

it('validates empty callback/inline ids', function () {
    $client = new FakeHttpClient();
    $bot = new TelegramBot('test', $client);

    expect(fn() => $bot->answerCallbackQuery(''))
        ->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);

    expect(fn() => $bot->answerInlineQuery('', []))
        ->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
});

