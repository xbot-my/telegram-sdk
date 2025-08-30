<?php

declare(strict_types=1);

use XBot\Telegram\Handlers\CommandRouter;
use XBot\Telegram\Utils\UpdateDispatcher;

class ProbeCommandHandler extends CommandRouter
{
    public array $calls = [];

    protected function onStart(array $u): void { $this->calls[] = ['start']; }
    protected function onHelp(array $u, string ...$args): void { $this->calls[] = array_merge(['help'], $args); }
    protected function onEcho(array $u, string ...$args): void { $this->calls[] = array_merge(['echo'], $args); }
    protected function onCommand(array $u, string $cmd, array $args): void { $this->calls[] = array_merge(['unknown:'.$cmd], $args); }
}

it('routes slash commands to methods with args', function () {
    $h = new ProbeCommandHandler();
    $d = new UpdateDispatcher([$h]);

    $d->dispatch(['update_id' => 1, 'message' => ['text' => '/start']]);
    $d->dispatch(['update_id' => 2, 'message' => ['text' => '/help how to use']]);
    $d->dispatch(['update_id' => 3, 'message' => ['text' => '/echo hello world']]);
    $d->dispatch(['update_id' => 4, 'message' => ['text' => '/noop@MyBot arg']]);

    expect($h->calls)->toBe([
        ['start'],
        ['help', 'how', 'to', 'use'],
        ['echo', 'hello', 'world'],
        ['unknown:noop', 'arg'],
    ]);
});

