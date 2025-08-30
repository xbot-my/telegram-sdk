<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendVideo extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $video, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'video'   => $video,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendVideo', $parameters, $files)
            : $this->call('sendVideo', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

