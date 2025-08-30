<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetChatAdministrators extends BaseEndpoint
{
    public function __invoke(int|string $chatId): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('getChatAdministrators', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

