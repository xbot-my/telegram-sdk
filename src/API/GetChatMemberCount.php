<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetChatMemberCount extends BaseEndpoint
{
    public function __invoke(int|string $chatId): int
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('getChatMemberCount', $parameters);
        return (int) $response->getResult();
    }
}
