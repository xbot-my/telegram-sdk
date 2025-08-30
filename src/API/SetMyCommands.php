<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetMyCommands extends BaseEndpoint
{
    public function __invoke(array $commands, array $options = []): bool
    {
        $this->validateRequired(['commands' => $commands], ['commands']);

        $parameters = array_merge([
            'commands' => $commands,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('setMyCommands', $parameters);
        return (bool) $response->getResult();
    }
}

