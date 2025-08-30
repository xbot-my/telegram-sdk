<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class CloseGeneralForumTopic extends BaseEndpoint
{
    public function __invoke(int|string $chatId): bool
    {
        $this->validateChatId($chatId);
        $parameters = $this->prepareParameters(['chat_id' => $chatId]);
        $response = $this->call('closeGeneralForumTopic', $parameters);
        return (bool)$response->getResult();
    }
}

