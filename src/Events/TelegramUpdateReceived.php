<?php

declare(strict_types=1);

namespace XBot\Telegram\Events;

final class TelegramUpdateReceived
{
    public array $update;

    public function __construct(array $update)
    {
        $this->update = $update;
    }
}

