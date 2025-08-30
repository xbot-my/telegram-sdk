<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendVoice extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $voice, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'voice'   => $voice,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendVoice', $parameters, $files)
            : $this->call('sendVoice', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

