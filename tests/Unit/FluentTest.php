<?php
declare(strict_types=1);

use XBot\Telegram\Fluent\Fluent;
use XBot\Telegram\Models\DTO\User;

it('wraps arrays and supports magic access', function () {
    $data = [
        'message' => [
            'chat' => [
                'id' => 123,
            ],
            'text' => 'hello',
        ],
    ];

    $f = new Fluent($data);

    expect($f->isMessage())->toBeTrue();
    expect($f->message->chat->id)->toBe(123);
    expect($f['message']->text)->toBe('hello');
    expect(isset($f->message))->toBeTrue();
});

it('converts to array/json and iterates', function () {
    $f = new Fluent(['a' => ['b' => 1]]);
    expect($f->toArray())->toBe(['a' => ['b' => 1]]);

    $json = $f->toJson();
    expect($json)->toBe('{"a":{"b":1}}');

    $items = [];
    foreach ($f as $k => $v) { $items[$k] = $v instanceof Fluent ? $v->toArray() : $v; }
    expect($items)->toBe(['a' => ['b' => 1]]);
});

it('casts to DTO via as()', function () {
    $u = (new Fluent(['id' => 42, 'is_bot' => true]))->as(User::class);
    expect($u)->toBeInstanceOf(User::class);
    expect($u->id)->toBe(42);
    expect($u->isBot())->toBeTrue();
});

