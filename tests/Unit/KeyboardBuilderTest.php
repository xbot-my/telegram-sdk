<?php

declare(strict_types=1);

use XBot\Telegram\Keyboard\InlineKeyboardBuilder as IK;
use XBot\Telegram\Keyboard\ReplyKeyboardBuilder as RK;
use XBot\Telegram\TelegramBot;
use XBot\Telegram\Tests\Support\FakeHttpClient;

it('builds inline keyboard with builder', function () {
    $ik = IK::make()->row(
        IK::button('A', ['callback_data' => 'a']),
        IK::button('B', ['url' => 'https://example.com'])
    );
    $markup = $ik->toArray();
    expect($markup)->toHaveKey('inline_keyboard');
    expect($markup['inline_keyboard'][0][0]['text'])->toBe('A');
});

it('builds reply keyboard with builder and sends', function () {
    $rk = RK::make()->row(
        RK::button('Yes'),
        RK::button('No', ['request_contact' => true])
    )->resize()->oneTime()->placeholder('type...');

    $client = new FakeHttpClient(handler: function ($method, $params) use ($rk) {
        expect($params['reply_markup'])->toBe($rk->toArray());
        return ['ok' => true, 'result' => ['message_id' => 1, 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });
    $bot = new TelegramBot('t', $client);
    $bot->sendMessage(1, 'hi', ['reply_markup' => $rk->toArray()]);
});

it('BotMessage accepts builders', function () {
    $ik = IK::make()->row(IK::button('A', ['callback_data' => 'a']));
    $rk = RK::make()->row(RK::button('Yes'))->resize();

    $markups = [];
    $client = new FakeHttpClient(handler: function ($method, $params) use (&$markups) {
        $markups[] = $params['reply_markup'] ?? null;
        return ['ok' => true, 'result' => ['message_id' => rand(1,9), 'date' => time(), 'chat' => ['id' => 1, 'type' => 'private']]];
    });
    $bot = new TelegramBot('t', $client);

    (new \XBot\Telegram\BotMessage($bot))->to(1)->keyboard($ik)->message('hi');
    (new \XBot\Telegram\BotMessage($bot))->to(1)->replyKeyboard($rk)->message('hi');

    expect($markups[0])->toHaveKey('inline_keyboard');
    expect($markups[1])->toHaveKey('keyboard');
});

