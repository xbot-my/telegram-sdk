<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

interface UpdateHandler
{
    /**
     * Handle a Telegram update payload (decoded array).
     */
    public function handle(array $update): void;
}

