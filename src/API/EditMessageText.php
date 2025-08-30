<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class EditMessageText extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageId, string $text, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);
        $this->validateTextLength($text, 4096);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => $text,
        ], $options));

        $response = $this->call('editMessageText', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

