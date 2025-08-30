<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class CopyMessage extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int|string $fromChatId, int $messageId, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateChatId($fromChatId);
        $this->validateMessageId($messageId);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id'      => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id'   => $messageId,
        ], $options));

        $response = $this->call('copyMessage', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
