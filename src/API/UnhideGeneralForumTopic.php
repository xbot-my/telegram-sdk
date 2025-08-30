<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class UnhideGeneralForumTopic extends BaseEndpoint
{
    public function __invoke(int|string $chatId): bool
    {
        $this->validateChatId($chatId);
        $parameters = $this->prepareParameters(['chat_id' => $chatId]);
        $response = $this->call('unhideGeneralForumTopic', $parameters);
        return (bool)$response->getResult();
    }
}

