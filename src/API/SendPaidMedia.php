<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendPaidMedia extends BaseEndpoint
{
    public function __invoke(int|string $chatId, array $media, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['media' => $media], ['media']);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'media'   => $media,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendPaidMedia', $parameters, $files)
            : $this->call('sendPaidMedia', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

