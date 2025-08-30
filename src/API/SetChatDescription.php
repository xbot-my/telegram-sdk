<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetChatDescription extends BaseEndpoint
{
    public function __invoke(int|string $chatId, ?string $description = null): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id'     => $chatId,
            'description' => $description,
        ]);

        $response = $this->call('setChatDescription', $parameters);
        return (bool) $response->getResult();
    }
}
