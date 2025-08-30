<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class ApproveSuggestedPost extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageId): bool
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);
        $parameters = $this->prepareParameters(['chat_id' => $chatId, 'message_id' => $messageId]);
        $response = $this->call('approveSuggestedPost', $parameters);
        return (bool)$response->getResult();
    }
}

