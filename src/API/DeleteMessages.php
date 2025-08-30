<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class DeleteMessages extends BaseEndpoint
{
    public function __invoke(int|string $chatId, array $messageIds): bool
    {
        $this->validateChatId($chatId);
        if (empty($messageIds)) {
            throw new \InvalidArgumentException('Message IDs array cannot be empty');
        }
        foreach ($messageIds as $id) {
            $this->validateMessageId((int) $id);
        }

        $parameters = $this->prepareParameters([
            'chat_id'     => $chatId,
            'message_ids' => $messageIds,
        ]);

        $response = $this->call('deleteMessages', $parameters);
        return (bool) $response->getResult();
    }
}

