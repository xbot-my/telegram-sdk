<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class StopPoll extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageId, ?array $replyMarkup = null): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = $this->prepareParameters([
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'reply_markup' => $replyMarkup,
        ]);

        $response = $this->call('stopPoll', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

