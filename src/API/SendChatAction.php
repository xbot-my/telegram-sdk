<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendChatAction extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $action): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['action' => $action], ['action']);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'action'  => $action,
        ]);

        $response = $this->call('sendChatAction', $parameters);
        return (bool)$response->getResult();
    }
}

