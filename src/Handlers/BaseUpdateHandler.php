<?php

declare(strict_types=1);

namespace XBot\Telegram\Handlers;

use XBot\Telegram\Contracts\UpdateHandler as UpdateHandlerContract;
use XBot\Telegram\Utils\UpdateRouter;
use XBot\Telegram\Bot;

abstract class BaseUpdateHandler implements UpdateHandlerContract
{
    protected ?Bot $bot;

    public function __construct(?Bot $bot = null)
    {
        $this->bot = $bot;
    }

    public function supports(array $update): bool
    {
        return true;
    }

    public function handle(array $update): void
    {
        $this->route($update);
    }

    protected function route(array $update): void
    {
        $type = UpdateRouter::detectType($update);
        if ($type === null) {
            $this->onUpdate($update);
            return;
        }

        $method = 'on' . UpdateRouter::studly($type);
        if (method_exists($this, $method)) {
            $this->{$method}($update);
            return;
        }

        $this->onUpdate($update);
    }

    // Default catch-all
    protected function onUpdate(array $update): void
    {
        // no-op by default
    }

    // Common helpers
    protected function chatId(array $update): int|string|null { return UpdateRouter::chatId($update); }
    protected function userId(array $update): ?int { return UpdateRouter::userId($update); }
    protected function text(array $update): ?string { return UpdateRouter::text($update); }

    // Reply helpers (no-op if bot not provided)
    protected function replyText(array $update, string $text, array $options = []): void
    {
        if (!$this->bot) { return; }
        $chatId = $this->chatId($update);
        if ($chatId === null) { return; }
        try { $this->bot->sendMessage($chatId, $text, $options); } catch (\Throwable) { /* ignore */ }
    }
}
