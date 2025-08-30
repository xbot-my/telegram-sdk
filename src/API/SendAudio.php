<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendAudio extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $audio, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'audio'   => $audio,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendAudio', $parameters, $files)
            : $this->call('sendAudio', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

