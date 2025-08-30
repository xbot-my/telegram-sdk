<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class ForwardMessages extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int|string $fromChatId, array $messageIds, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateChatId($fromChatId);
        if (empty($messageIds)) {
            throw new \InvalidArgumentException('Message IDs array cannot be empty');
        }
        foreach ($messageIds as $id) {
            $this->validateMessageId((int) $id);
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id'      => $chatId,
            'from_chat_id' => $fromChatId,
            'message_ids'  => $messageIds,
        ], $options));

        $response = $this->call('forwardMessages', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

