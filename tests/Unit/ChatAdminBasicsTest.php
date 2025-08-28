<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('gets chat administrators', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        expect($method)->toBe('getChatAdministrators');
        expect($params['chat_id'])->toBe(1);
        return ['ok' => true, 'result' => [['user' => ['id' => 2], 'status' => 'administrator']]];
    });
    $bot = new TelegramBot('t', $client);
    $admins = $bot->getChatAdministrators(1);
    expect($admins)->toBeArray()->and($admins[0]['status'])->toBe('administrator');
});

it('sets and deletes chat photo (upload path)', function () {
    $photo = tempnam(sys_get_temp_dir(), 'p');
    file_put_contents($photo, 'IMG');
    $client = new FakeHttpClient(handler: function ($method, $params, $files) use ($photo) {
        if ($method === 'setChatPhoto') {
            expect($files)->toHaveKey('photo');
            expect($files['photo'])->toBe($photo);
            return ['ok' => true, 'result' => true];
        }
        if ($method === 'deleteChatPhoto') {
            return ['ok' => true, 'result' => true];
        }
        return ['ok' => false, 'error_code' => 400, 'description' => 'unexpected'];
    });
    $bot = new TelegramBot('t', $client);
    expect($bot->setChatPhoto(1, $photo))->toBeTrue();
    expect($bot->deleteChatPhoto(1))->toBeTrue();
});

it('sets chat title/description with validation', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        if ($method === 'setChatTitle') {
            expect($params['title'])->toBe('New Title');
        }
        if ($method === 'setChatDescription') {
            expect($params['description'])->toBe('desc');
        }
        return ['ok' => true, 'result' => true];
    });
    $bot = new TelegramBot('t', $client);
    expect($bot->setChatTitle(1, 'New Title'))->toBeTrue();
    expect($bot->setChatDescription(1, 'desc'))->toBeTrue();
    expect(fn() => $bot->setChatTitle(1, ''))
        ->toThrow(\XBot\Telegram\Exceptions\ValidationException::class);
});

it('pins/unpins messages and leaves chat', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        return ['ok' => true, 'result' => true];
    });
    $bot = new TelegramBot('t', $client);
    expect($bot->pinChatMessage(1, 10, true))->toBeTrue();
    expect($bot->unpinChatMessage(1, 10))->toBeTrue();
    expect($bot->unpinAllChatMessages(1))->toBeTrue();
    expect($bot->leaveChat(1))->toBeTrue();
});

