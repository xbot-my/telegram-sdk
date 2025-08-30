<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class DeleteMyCommands extends BaseEndpoint
{
    public function __invoke(array $options = []): bool
    {
        $parameters = $this->prepareParameters($options);
        $response = $this->call('deleteMyCommands', $parameters);
        return (bool) $response->getResult();
    }
}

