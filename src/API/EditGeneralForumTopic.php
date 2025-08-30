<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class EditGeneralForumTopic extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $name): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['name' => $name], ['name']);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'name'    => $name,
        ]);

        $response = $this->call('editGeneralForumTopic', $parameters);
        return (bool)$response->getResult();
    }
}

