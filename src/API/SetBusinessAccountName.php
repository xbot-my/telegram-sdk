<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetBusinessAccountName extends BaseEndpoint
{
    public function __invoke(string $name): bool
    {
        $this->validateRequired(['name' => $name], ['name']);
        $parameters = $this->prepareParameters(['name' => $name]);
        $response = $this->call('setBusinessAccountName', $parameters);
        return (bool)$response->getResult();
    }
}

