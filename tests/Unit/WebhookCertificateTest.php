<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('uploads certificate when provided in setWebhook options', function () {
    $cert = tempnam(sys_get_temp_dir(), 'crt');
    file_put_contents($cert, 'CERT');

    $client = new FakeHttpClient(handler: function ($method, $params, $files) use ($cert) {
        expect($method)->toBe('setWebhook');
        expect($files)->toHaveKey('certificate');
        expect($files['certificate'])->toBe($cert);
        return ['ok' => true, 'result' => true];
    });

    $bot = new TelegramBot('test', $client);
    $ok = $bot->setWebhook('https://example/webhook', [
        'certificate' => $cert,
        'secret_token' => 'secret',
    ]);

    expect($ok)->toBeTrue();
});

