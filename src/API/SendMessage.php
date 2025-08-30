<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendMessage extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $text, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateTextLength($text, 4096);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'text'    => $text,
        ], $options));

        $response = $this->call('sendMessage', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
