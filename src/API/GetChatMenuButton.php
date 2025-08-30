<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetChatMenuButton extends BaseEndpoint
{
    public function __invoke(?int $chatId = null): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);
        $response = $this->call('getChatMenuButton', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

