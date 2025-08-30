<?php

declare(strict_types=1);

use XBot\Telegram\Http\Response\TelegramResponse;
use XBot\Telegram\Exceptions\ApiException;

it('wraps success response and exposes result', function () {
    $resp = TelegramResponse::success(['id' => 1, 'type' => 'private'], 200, ['X-Foo' => 'bar'], 'bot1');
    expect($resp->isOk())->toBeTrue()
        ->and($resp->isError())->toBeFalse()
        ->and($resp->getResult())
            ->toMatchArray(['id' => 1, 'type' => 'private'])
        ->and($resp->getStatusCode())->toBe(200)
        ->and($resp->getHeader('X-Foo'))->toBe('bar')
        ->and($resp->getBotName())->toBe('bot1');
});

it('throws on toDTO when error and supports ensureOk', function () {
    $resp = TelegramResponse::error('Bad request', 400, ['param' => 'chat_id'], 400, [], 'bot2');
    expect($resp->isOk())->toBeFalse()
        ->and($resp->isBadRequest())->toBeTrue()
        ->and($resp->getDescription())->toBe('Bad request')
        ->and($resp->getParameters())
            ->toMatchArray(['param' => 'chat_id']);

    expect(fn() => $resp->ensureOk())->toThrow(ApiException::class);
});

it('handles arrays of DTOs and helpers for rate limiting and migration', function () {
    $resp = TelegramResponse::success([
        ['id' => 1, 'type' => 'private'],
        ['id' => 2, 'type' => 'group'],
    ]);
    $result = $resp->getResult();
    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0]['id'])->toBe(1)
        ->and($result[1]['type'])->toBe('group');

    $err = TelegramResponse::error('Too Many Requests', 429, ['retry_after' => 2]);
    expect($err->isRateLimited())->toBeTrue()
        ->and($err->getRetryAfter())->toBe(2);

    $mig = TelegramResponse::error('migrated', 400, ['migrate_to_chat_id' => 12345]);
    expect($mig->isChatMigrated())->toBeTrue()
        ->and($mig->getMigrateToChatId())->toBe(12345);
});

it('serializes to array and json', function () {
    $resp = TelegramResponse::success(['ok' => true]);
    $arr = $resp->toArray();
    expect($arr)->toHaveKeys(['ok', 'result', 'error_code', 'description', 'parameters'])
        ->and(json_decode($resp->toJson(), true))
        ->toHaveKey('ok');
});
