<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetChatTitle extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $title): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['title' => $title], ['title']);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'title'   => $title,
        ]);

        $response = $this->call('setChatTitle', $parameters);
        return (bool) $response->getResult();
    }
}
