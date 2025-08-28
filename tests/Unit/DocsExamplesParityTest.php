<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('README inline keyboard chain produces expected payload', function () {
    $seen = null;
    $client = new FakeHttpClient(handler: function ($method, $params) use (&$seen) {
        $seen = $params;
        return ['ok' => true, 'result' => ['message_id' => 1, 'date' => time(), 'chat' => ['id' => 12345, 'type' => 'private']]];
    });

    $bot = new TelegramBot('readme', $client);

    (new \XBot\Telegram\BotMessage($bot))
        ->to(12345)
        ->html()
        ->keyboard([
            [['text' => 'Button 1', 'callback_data' => 'btn1']],
            [['text' => 'Button 2', 'callback_data' => 'btn2']],
        ])
        ->message('<b>Choose an option:</b>');

    expect($seen['parse_mode'] ?? null)->toBe('HTML');
    expect($seen['reply_markup']['inline_keyboard'][0][0]['callback_data'] ?? null)->toBe('btn1');
});

