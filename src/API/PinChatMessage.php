<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class PinChatMessage extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageId, array $options = []): bool
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = array_merge([
            'chat_id'    => $chatId,
            'message_id' => $messageId,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('pinChatMessage', $parameters);
        return (bool) $response->getResult();
    }
}
