<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class LogOut extends BaseEndpoint
{
    public function __invoke(): bool
    {
        $response = $this->call('logOut');
        return (bool) $response->getResult();
    }
}

