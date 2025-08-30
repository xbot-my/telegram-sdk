<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetMessageReaction extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageId, array $reaction = [], ?bool $isBig = null): bool
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);
        $parameters = $this->prepareParameters([
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'reaction'   => $reaction,
            'is_big'     => $isBig,
        ]);
        $response = $this->call('setMessageReaction', $parameters);
        return (bool)$response->getResult();
    }
}

