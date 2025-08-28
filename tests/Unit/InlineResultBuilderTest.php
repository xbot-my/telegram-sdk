<?php

declare(strict_types=1);

use XBot\Telegram\Inline\InlineResultBuilder as IR;
use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('builds inline query results and answers inline query', function () {
    $results = [
        IR::article('1', 'Hello', IR::markdown('*Hi*')),
        IR::photo('2', 'https://example.com/p.jpg', 'https://example.com/t.jpg', ['caption' => 'cap']),
        IR::gif('3', 'https://example.com/a.gif', 'https://example.com/t.jpg'),
        IR::mpeg4Gif('4', 'https://example.com/a.mp4', 'https://example.com/t.jpg'),
        IR::video('5', 'https://example.com/v.mp4', 'video/mp4', 'Title', 'https://example.com/t.jpg'),
    ];

    $client = new FakeHttpClient(handler: function ($method, $params) use ($results) {
        expect($method)->toBe('answerInlineQuery');
        expect($params['inline_query_id'])->toBe('IQ');
        expect($params['results'])->toBe($results);
        return ['ok' => true, 'result' => true];
    });

    $bot = new TelegramBot('t', $client);
    expect($bot->answerInlineQuery('IQ', $results))->toBeTrue();
});

