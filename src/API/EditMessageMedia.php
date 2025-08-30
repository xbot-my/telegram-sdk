<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class EditMessageMedia extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $messageId, array $media, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = array_merge([
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'media'      => $media,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('editMessageMedia', $parameters, $files)
            : $this->call('editMessageMedia', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

