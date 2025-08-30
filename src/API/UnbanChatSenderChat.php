<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class UnbanChatSenderChat extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $senderChatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id'        => $chatId,
            'sender_chat_id' => $senderChatId,
        ]);

        $response = $this->call('unbanChatSenderChat', $parameters);
        return (bool) $response->getResult();
    }
}

