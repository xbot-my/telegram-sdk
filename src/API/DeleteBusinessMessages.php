<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class DeleteBusinessMessages extends BaseEndpoint
{
    public function __invoke(int|string $chatId, array $messageIds): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['message_ids' => $messageIds], ['message_ids']);
        $parameters = $this->prepareParameters(['chat_id' => $chatId, 'message_ids' => $messageIds]);
        $response = $this->call('deleteBusinessMessages', $parameters);
        return (bool)$response->getResult();
    }
}

