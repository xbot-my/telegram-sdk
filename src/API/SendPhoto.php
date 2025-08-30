<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendPhoto extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $photo, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'photo'   => $photo,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendPhoto', $parameters, $files)
            : $this->call('sendPhoto', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
