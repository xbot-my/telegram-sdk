<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class PromoteChatMember extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $userId, array $options = []): bool
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options));

        $response = $this->call('promoteChatMember', $parameters);
        return (bool) $response->getResult();
    }
}

