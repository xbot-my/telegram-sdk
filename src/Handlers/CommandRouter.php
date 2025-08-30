<?php

declare(strict_types=1);

namespace XBot\Telegram\Handlers;

use XBot\Telegram\Utils\CommandParser;
use XBot\Telegram\Utils\UpdateRouter;

abstract class CommandRouter extends BaseUpdateHandler
{
    public function supports(array $update): bool
    {
        $text = UpdateRouter::text($update);
        return is_string($text) && str_starts_with(trim($text), '/');
    }

    protected function onMessage(array $update): void
    {
        $this->routeCommand($update);
    }

    protected function onEditedMessage(array $update): void
    {
        $this->routeCommand($update);
    }

    protected function onChannelPost(array $update): void
    {
        $this->routeCommand($update);
    }

    protected function routeCommand(array $update): void
    {
        $parsed = CommandParser::parse(UpdateRouter::text($update));
        if ($parsed === null) {
            $this->onUpdate($update);
            return;
        }

        $command = $parsed['command'];
        $args = $parsed['args'];

        $method = 'on' . ucfirst($command);
        if (method_exists($this, $method)) {
            $this->{$method}($update, ...$args);
            return;
        }

        $this->onCommand($update, $command, $args);
    }

    protected function onCommand(array $update, string $command, array $args): void
    {
        // Default no-op; override to handle unknown commands.
    }
}

