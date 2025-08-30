<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class UnpinAllForumTopicMessages extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageThreadId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id'           => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);

        $response = $this->call('unpinAllForumTopicMessages', $parameters);
        return (bool)$response->getResult();
    }
}

