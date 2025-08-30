<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendSticker extends BaseEndpoint
{
    public function __invoke(int|string $chatId, $sticker, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'sticker' => $sticker,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendSticker', $parameters, $files)
            : $this->call('sendSticker', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

