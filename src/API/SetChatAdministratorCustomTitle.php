<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetChatAdministratorCustomTitle extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $userId, string $customTitle): bool
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);
        $this->validateTextLength($customTitle, 16);

        $parameters = $this->prepareParameters([
            'chat_id'      => $chatId,
            'user_id'      => $userId,
            'custom_title' => $customTitle,
        ]);

        $response = $this->call('setChatAdministratorCustomTitle', $parameters);
        return (bool) $response->getResult();
    }
}

