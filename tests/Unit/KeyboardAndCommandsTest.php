<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;
use XBot\Telegram\Bot;

it('BotMessage builds inline and reply keyboards and removal/forceReply', function () {
    $sent = [];
    $client = new FakeHttpClient(handler: function ($method, $params) use (&$sent) {
        $sent[] = $params['reply_markup'] ?? null;
        return ['ok' => true, 'result' => ['message_id' => rand(1, 1000), 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });

    Bot::useManager(new \XBot\Telegram\BotManager(['default' => 'd', 'bots' => ['d' => ['token' => 'T']]]));
    // Inject bot into Bot facade-like entry by replacing via quick call
    $bot = new TelegramBot('d', $client);

    // Inline keyboard
    (new \XBot\Telegram\BotMessage($bot))->to(1)
        ->inlineKeyboard([[['text' => 'A', 'callback_data' => 'a']]])
        ->message('hi');

    // Reply keyboard
    (new \XBot\Telegram\BotMessage($bot))->to(1)
        ->replyKeyboard([[['text' => 'Yes']],[['text' => 'No']]], ['resize_keyboard' => true])
        ->message('choose');

    // Remove keyboard
    (new \XBot\Telegram\BotMessage($bot))->to(1)
        ->removeKeyboard(true)
        ->message('remove');

    // Force reply
    (new \XBot\Telegram\BotMessage($bot))->to(1)
        ->forceReply(true, 'type here')
        ->message('reply');

    expect($sent[0])->toHaveKey('inline_keyboard');
    expect($sent[1])->toHaveKey('keyboard');
    expect($sent[1]['resize_keyboard'])->toBeTrue();
    expect($sent[2])->toMatchArray(['remove_keyboard' => true, 'selective' => true]);
    expect($sent[3])->toMatchArray(['force_reply' => true, 'selective' => true, 'input_field_placeholder' => 'type here']);
});

it('sets/gets/deletes bot commands', function () {
    $calls = [];
    $client = new FakeHttpClient(handler: function ($method, $params) use (&$calls) {
        $calls[] = $method;
        if ($method === 'setMyCommands') {
            expect($params['commands'][0]['command'])->toBe('start');
            return ['ok' => true, 'result' => true];
        }
        if ($method === 'getMyCommands') {
            return ['ok' => true, 'result' => [['command' => 'start', 'description' => 'Start']]];
        }
        if ($method === 'deleteMyCommands') {
            return ['ok' => true, 'result' => true];
        }
        return ['ok' => false, 'error_code' => 400, 'description' => 'unexpected'];
    });

    $bot = new TelegramBot('t', $client);
    $ok = $bot->setMyCommands([
        ['command' => 'start', 'description' => 'Start'],
    ]);
    expect($ok)->toBeTrue();
    $cmds = $bot->getMyCommands();
    expect($cmds[0]['command'])->toBe('start');
    expect($bot->deleteMyCommands())->toBeTrue();
    expect($calls)->toContain('setMyCommands');
    expect($calls)->toContain('getMyCommands');
    expect($calls)->toContain('deleteMyCommands');
});

it('validates invalid commands array', function () {
    $bot = new TelegramBot('t', new FakeHttpClient());
    expect(fn() => $bot->setMyCommands([]))
        ->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
    expect(fn() => $bot->setMyCommands([['command' => 'INVALID UPPER', 'description' => 'x']]))
        ->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
});

