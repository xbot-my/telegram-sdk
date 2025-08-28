<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('gets file info and user profile photos', function () {
    $client = new FakeHttpClient(handler: function ($method, $params) {
        if ($method === 'getFile') {
            expect($params['file_id'])->toBe('FILE123');
            return ['ok' => true, 'result' => ['file_id' => 'FILE123', 'file_path' => 'photos/file.jpg']];
        }
        if ($method === 'getUserProfilePhotos') {
            expect($params['user_id'])->toBe(42);
            expect($params['limit'])->toBe(2);
            return ['ok' => true, 'result' => ['total_count' => 1, 'photos' => [[['file_id' => 'A']]]]];
        }
        return ['ok' => false, 'error_code' => 400, 'description' => 'unexpected'];
    });

    $bot = new TelegramBot('t', $client);
    $file = $bot->getFile('FILE123');
    expect($file['file_path'])->toBe('photos/file.jpg');

    $photos = $bot->getUserProfilePhotos(42, ['limit' => 2]);
    expect($photos['total_count'])->toBe(1);
});

