<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class EditForumTopic extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageThreadId, array $options = []): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['message_thread_id' => $messageThreadId], ['message_thread_id']);

        $parameters = array_merge([
            'chat_id'           => $chatId,
            'message_thread_id' => $messageThreadId,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('editForumTopic', $parameters);
        return (bool)$response->getResult();
    }
}

