<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendVideoNote extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $videoNote, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id'   => $chatId,
            'video_note'=> $videoNote,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendVideoNote', $parameters, $files)
            : $this->call('sendVideoNote', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

