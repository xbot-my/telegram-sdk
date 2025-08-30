<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetChatMenuButton extends BaseEndpoint
{
    public function __invoke(array $options = []): bool
    {
        $parameters = $this->prepareParameters($options);
        $response = $this->call('setChatMenuButton', $parameters);
        return (bool) $response->getResult();
    }
}

