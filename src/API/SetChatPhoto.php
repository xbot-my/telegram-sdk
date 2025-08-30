<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetChatPhoto extends BaseEndpoint
{
    public function __invoke(int|string $chatId, $photo): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['photo' => $photo], ['photo']);

        $parameters = [
            'chat_id' => $chatId,
            'photo'   => $photo,
        ];

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('setChatPhoto', $parameters, $files)
            : $this->call('setChatPhoto', $parameters);

        return (bool)$response->getResult();
    }
}

