<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class ApproveChatJoinRequest extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $userId): bool
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);
        $parameters = $this->prepareParameters(['chat_id' => $chatId, 'user_id' => $userId]);
        $response = $this->call('approveChatJoinRequest', $parameters);
        return (bool)$response->getResult();
    }
}

