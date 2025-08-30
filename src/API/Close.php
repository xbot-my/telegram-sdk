<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class Close extends BaseEndpoint
{
    public function __invoke(): bool
    {
        $response = $this->call('close');
        return (bool) $response->getResult();
    }
}

