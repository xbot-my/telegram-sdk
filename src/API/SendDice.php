<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendDice extends BaseEndpoint
{
    public function __invoke(int|string $chatId, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
        ], $options));

        $response = $this->call('sendDice', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

