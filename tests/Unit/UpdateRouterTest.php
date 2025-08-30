<?php

declare(strict_types=1);

use XBot\Telegram\Handlers\BaseUpdateHandler;
use XBot\Telegram\Utils\UpdateDispatcher;

class ProbeHandler extends BaseUpdateHandler
{
    public array $calls = [];
    protected function onMessage(array $u): void { $this->calls[] = 'message'; }
    protected function onCallbackQuery(array $u): void { $this->calls[] = 'callback_query'; }
    protected function onUpdate(array $u): void { $this->calls[] = 'update'; }
}

it('routes to specific handler methods by update type', function () {
    $h = new ProbeHandler();
    $d = new UpdateDispatcher([$h]);

    $d->dispatch(['update_id' => 1, 'message' => ['chat' => ['id' => 1], 'text' => 'hi']]);
    $d->dispatch(['update_id' => 2, 'callback_query' => ['from' => ['id' => 9], 'data' => 'x']]);
    $d->dispatch(['update_id' => 3, 'unknown' => []]);

    expect($h->calls)->toBe(['message', 'callback_query', 'update']);
});

