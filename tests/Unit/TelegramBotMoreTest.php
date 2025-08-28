<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('forwards and copies messages', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        if ($method === 'forwardMessage') {
            expect($params['from_chat_id'])->toBe(1);
            expect($params['chat_id'])->toBe(2);
            expect($params['message_id'])->toBe(10);
            return ['ok' => true, 'result' => ['message_id' => 11, 'date' => time(), 'chat' => ['id' => 2, 'type' => 'private']]];
        }
        if ($method === 'copyMessage') {
            expect($params['from_chat_id'])->toBe(1);
            expect($params['chat_id'])->toBe(2);
            expect($params['message_id'])->toBe(10);
            return ['ok' => true, 'result' => ['message_id' => 12]];
        }
        return ['ok' => false, 'error_code' => 400, 'description' => 'unexpected'];
    });

    $bot = new TelegramBot('test', $client);
    $fwd = $bot->forwardMessage(2, 1, 10);
    expect($fwd->messageId)->toBe(11);

    $copyId = $bot->copyMessage(2, 1, 10);
    expect($copyId)->toBe(12);
});

it('returns chat member count', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('getChatMemberCount');
        expect($params['chat_id'])->toBe(123);
        return ['ok' => true, 'result' => 42];
    });

    $bot = new TelegramBot('test', $client);
    expect($bot->getChatMemberCount(123))->toBe(42);
});

it('validates admin operations parameters', function () {
    $client = new FakeHttpClient();
    $bot = new TelegramBot('test', $client);

    // invalid user id
    expect(fn() => $bot->banChatMember(1, 0))->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
    expect(fn() => $bot->restrictChatMember(1, -1, []))->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
});

it('uploads other media types via upload branch', function () {
    $tmp = tempnam(sys_get_temp_dir(), 'tg');
    file_put_contents($tmp, 'bin');
    $seen = [];
    $client = new FakeHttpClient(handler: function ($method, $params, $files) use (&$seen, $tmp) {
        $seen[] = [$method, $files];
        return ['ok' => true, 'result' => ['message_id' => rand(100, 999), 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });

    $bot = new TelegramBot('test', $client);
    $bot->sendDocument(1, $tmp);
    $bot->sendAudio(1, $tmp);
    $bot->sendVoice(1, $tmp);
    $bot->sendAnimation(1, $tmp);

    expect($seen)->each(function ($entry) use ($tmp) {
        [$method, $files] = $entry;
        expect($files)->not->toBeEmpty();
        expect(in_array($tmp, $files, true))->toBeTrue();
    });
});

