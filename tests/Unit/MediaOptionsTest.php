<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

function _tmpfile_with(string $content): string {
    $tmp = tempnam(sys_get_temp_dir(), 'tg');
    file_put_contents($tmp, $content);
    return $tmp;
}

it('passes options for sendPhoto and triggers upload for local file', function () {
    $file = _tmpfile_with('img');
    $client = new FakeHttpClient(handler: function ($method, $params, $files) use ($file) {
        expect($method)->toBe('sendPhoto');
        expect($params['caption'])->toBe('cap');
        expect($params['parse_mode'])->toBe('HTML');
        expect($params['disable_notification'])->toBe('true');
        expect($files)->toHaveKey('photo');
        expect($files['photo'])->toBe($file);
        return ['ok' => true, 'result' => ['message_id' => 1, 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });
    $bot = new TelegramBot('t', $client);
    $msg = $bot->message->sendPhoto(1, $file, ['caption' => 'cap', 'parse_mode' => 'HTML', 'disable_notification' => true]);
    expect($msg->messageId)->toBe(1);
});

it('passes options for other media endpoints', function () {
    $file = _tmpfile_with('bin');
    $seen = [];
    $client = new FakeHttpClient(handler: function ($method, $params, $files) use (&$seen, $file) {
        $seen[$method] = ['params' => $params, 'files' => $files];
        return ['ok' => true, 'result' => ['message_id' => rand(10, 999), 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });
    $bot = new TelegramBot('t', $client);

    $opts = ['caption' => 'c', 'parse_mode' => 'Markdown', 'disable_notification' => true];
    $bot->message->sendVideo(1, $file, $opts);
    $bot->message->sendAudio(1, $file, $opts);
    $bot->message->sendDocument(1, $file, $opts);
    $bot->message->sendVoice(1, $file, $opts);
    $bot->message->sendAnimation(1, $file, $opts);

    foreach (['sendVideo','sendAudio','sendDocument','sendVoice','sendAnimation'] as $m) {
        expect($seen[$m]['params']['caption'] ?? null)->toBe('c');
        expect($seen[$m]['params']['parse_mode'] ?? null)->toBe('Markdown');
        expect($seen[$m]['params']['disable_notification'] ?? null)->toBe('true');
        expect($seen[$m]['files'])->not->toBeEmpty();
        // ensure upload path used
        expect(in_array($file, $seen[$m]['files'], true))->toBeTrue();
    }
});

it('sendLocation and sendContact pass options', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        if ($method === 'sendLocation') {
            expect($params['latitude'])->toBe(10.0);
            expect($params['longitude'])->toBe(20.0);
            expect($params['disable_notification'])->toBe('true');
        }
        if ($method === 'sendContact') {
            expect($params['phone_number'])->toBe('123');
            expect($params['first_name'])->toBe('Jane');
            expect($params['last_name'])->toBe('Doe');
        }
        return ['ok' => true, 'result' => ['message_id' => rand(1000, 2000), 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });
    $bot = new TelegramBot('t', $client);
    $bot->message->sendLocation(1, 10.0, 20.0, ['disable_notification' => true]);
    $bot->message->sendContact(1, '123', 'Jane', ['last_name' => 'Doe']);
    expect(true)->toBeTrue();
});

