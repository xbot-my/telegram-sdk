<?php

declare(strict_types=1);

use XBot\Telegram\Contracts\UpdateHandler;
use XBot\Telegram\Utils\UpdateDispatcher;

class DummyUpdateHandler implements UpdateHandler
{
    public static int $called = 0;
    public function handle(array $update): void
    {
        self::$called++;
    }
}

it('dispatches update to handlers', function () {
    DummyUpdateHandler::$called = 0;
    $d = new UpdateDispatcher([new DummyUpdateHandler()]);
    $d->dispatch(['update_id' => 1, 'message' => ['text' => 'hi']]);
    expect(DummyUpdateHandler::$called)->toBe(1);
});

